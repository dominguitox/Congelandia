<?php

namespace Congelandia\Migration;

/** Clasificación propuesta a partir del texto; no modifica el respaldo original. */
final class ProductCategories
{
    public static function suggest(string $name, string $code): array
    {
        $text = strtr(mb_strtolower(trim($name), 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
        $text = preg_replace('/\s+/', ' ', $text);
        $review = [
            'carolina' => ['Por clasificar', 'Nombre insuficiente para identificar el producto.'],
            'pina colada' => ['Helados', 'Variedad junto a otros helados del catálogo; confirmar.'],
            'huevos 16 un oro' => ['Snacks y dulces', 'Presentación posiblemente de chocolate; confirmar.'],
            'huevos 30 uni' => ['Snacks y dulces', 'Presentación junto a huevos de chocolate; confirmar.'],
        ];
        if (isset($review[$text])) {
            return ['category' => $review[$text][0], 'reason' => $review[$text][1], 'review' => true];
        }
        if ($text === 'popcorn' && str_starts_with($code, '780961')) {
            return ['category' => 'Pollo', 'reason' => 'Código de la familia de productos de pollo; confirmar descripción.', 'review' => true];
        }
        $rules = [
            'Helados' => '/helado|cass?ata/',
            'Panadería y pastelería' => '/\bpan\b|molde|tostad|tapadito|queque|brownie|panetone|churro|macaron|donuts|masa empanada/',
            'Comidas preparadas' => '/empanada|arrollado|gyosa|gyoza|giosa|sopai|pizza|bar[ir]?[it]as de queso|tortilla/',
            'Hamburguesas' => '/hamburguesa|hamb pol|hamburguesa soya/',
            'Pescados' => '/atun|merluza|pangasius|salmon|reineta|tilapia|pescado/',
            'Mariscos' => '/camaron|ostion|kanikama|calama?r|almeja|paila marina|maris|chorito|jai[bv]a/',
            'Pollo' => '/pollo|pechuga|trutro|alita|filetito|nugget|ch[ie]c?ken finger|chiken finger/',
            'Cecinas y embutidos' => '/salchicha|jamon|chorizo|longaniza|prieta/',
            'Carnes' => '/chuleta|chuleton|pulpa|malaya|entrana|albondiga|churrasco|lomito|lomo|vacuno|cerdo|bistec/',
            'Frutas y pulpas' => '/frambuesa|arandano|mango trozo|pina trozo|duraznos cubos/',
            'Papas y vegetales' => '/papas?|choclo|primavera|arveja|sofrito|cebolla|habas?|champinon|chapsui|poroto verde|edamame/',
            'Bebidas y aguas' => '/coca cola|pepsi|fanta|sprite|cachantun|vital|bilz|\bpap\b|\bkem\b|\bscore\b|jugo zuko/',
            'Snacks y dulces' => '/tic tac|mentos|sufle|ramita|mani\b|\bm\s*&\s*m\b|bon bon|popcorn|galleta|krisp|zucarita|peelerz|huevos choc|push pop|cracker|alfajor|chupa chups|pringles/',
            'Lácteos y huevos' => '/queso|mantequilla|huevos/',
            'Abarrotes y condimentos' => '/alcaparra|aceituna|pickles|alga nori|panko|jugo de limon|salsa|harina|merken|mayonesa|ketchup/',
            'Parrilla' => '/carbon/',
        ];
        // Las pulpas de fruta se distinguen de los cortes llamados «pulpa».
        if (preg_match('/pulpa (framb|fruti|maracuya|mango|melon|pina)/', $text)) {
            return ['category' => 'Frutas y pulpas', 'reason' => 'Pulpa de fruta indicada en el nombre.', 'review' => false];
        }
        foreach ($rules as $category => $pattern) {
            if (preg_match($pattern, $text)) {
                return ['category' => $category, 'reason' => 'Clasificación propuesta según el nombre del producto.', 'review' => false];
            }
        }

        return ['category' => 'Por clasificar', 'reason' => 'No hay información suficiente en el nombre.', 'review' => true];
    }

    public static function apply(array $plan): array
    {
        foreach ($plan['products'] as &$product) {
            $suggestion = self::suggest($product['name'], $product['code']);
            $product['original_category'] = $product['category'];
            $product['category'] = $suggestion['review'] ? 'Sin categoría' : $suggestion['category'];
            $product['category_reason'] = $suggestion['reason'];
            $product['category_review'] = $suggestion['review'];
        }

        return $plan;
    }

    public static function sql(array $plan): string
    {
        $q = PdvMigration::sql(...);
        $version = substr(hash('sha256', json_encode(array_map(fn ($p) => [$p['code'], $p['category']], $plan['products']), JSON_THROW_ON_ERROR)), 0, 16);
        $stage = 'categorias-'.$version;
        $codes = implode(', ', array_map(fn ($p) => $q($p['code']), $plan['products']));
        $marker = $q('pdv:'.$plan['manifest']['source_sha256'].':catalogo');
        $sql = "-- Categorías propuestas por nombre, no categorías recuperadas del respaldo.\n"
            .PdvMigration::begin($plan, $stage, "EXISTS(SELECT 1 FROM registro_acciones WHERE referencia = $marker) AND (SELECT COUNT(*) FROM producto WHERE codigo IN ($codes)) = ".count($plan['products']));
        foreach (array_unique(array_column($plan['products'], 'category')) as $category) {
            $name = $q($category);
            $sql .= "INSERT INTO categoria (nombre) SELECT $name WHERE @pdv_apply = 1 AND NOT EXISTS(SELECT 1 FROM categoria WHERE nombre = $name AND deleted_at IS NULL);\n";
        }
        foreach ($plan['products'] as $product) {
            $sql .= 'UPDATE producto SET idCategoria = (SELECT MIN(idCategoria) FROM categoria WHERE nombre = '.$q($product['category']).' AND deleted_at IS NULL) WHERE codigo = '.$q($product['code'])." AND @pdv_apply = 1;\n";
        }
        foreach (array_unique(array_column($plan['products'], 'original_category')) as $original) {
            if (! in_array($original, array_column($plan['products'], 'category'), true)) {
                $sql .= 'UPDATE categoria SET deleted_at = NOW() WHERE nombre = '.$q($original)." AND @pdv_apply = 1 AND NOT EXISTS(SELECT 1 FROM producto WHERE producto.idCategoria = categoria.idCategoria);\n";
            }
        }

        return $sql.PdvMigration::finish($plan, $stage);
    }
}
