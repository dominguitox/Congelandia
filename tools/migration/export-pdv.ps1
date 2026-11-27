param(
    [Parameter(Mandatory=$true)][string]$Source,
    [string]$OutputDirectory = 'output/pdv-migration/origen',
    [string]$ToolsDirectory = 'tmp/pdv-migration/tools',
    [string]$Charset = 'WIN1252'
)
$ErrorActionPreference = 'Stop'
$Source = (Resolve-Path -LiteralPath $Source).Path
$OutputDirectory = [IO.Path]::GetFullPath($OutputDirectory)
$ToolsDirectory = [IO.Path]::GetFullPath($ToolsDirectory)
if ($Source.StartsWith($OutputDirectory + '\', [StringComparison]::OrdinalIgnoreCase)) {
    throw 'El archivo original debe estar fuera del directorio de salida.'
}

# ODS 10.1 de este PDV necesita el motor de 32 bits, incluso en Windows x64.
if ([Environment]::Is64BitProcess) {
    & "$env:WINDIR\SysWOW64\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -ExecutionPolicy Bypass -File $PSCommandPath -Source $Source -OutputDirectory $OutputDirectory -ToolsDirectory $ToolsDirectory -Charset $Charset
    exit $LASTEXITCODE
}
New-Item -ItemType Directory -Path $ToolsDirectory -Force | Out-Null
$packages = @(
    @{ Name='firebird-x86'; Url='https://github.com/FirebirdSQL/firebird/releases/download/R2_5_9/Firebird-2.5.9.27139-0_Win32_embed.zip'; Sha='D738C3C513A238CF65A17B642D213E57FD7F1D69D2A51FCD7BBFC3B802F6988D' },
    @{ Name='provider'; Url='https://api.nuget.org/v3-flatcontainer/firebirdsql.data.firebirdclient/5.12.1/firebirdsql.data.firebirdclient.5.12.1.nupkg'; Sha='DA7FBED90A9FE30B5F31F7B3C5A761B4316E4F1CD4A1DFA86628370EAE2D831C' }
)
foreach ($package in $packages) {
    $archive = Join-Path $ToolsDirectory ($package.Name + '.zip')
    if (!(Test-Path -LiteralPath $archive)) {
        Invoke-WebRequest -UseBasicParsing -Uri $package.Url -OutFile $archive
    }
    if ((Get-FileHash -LiteralPath $archive -Algorithm SHA256).Hash -ne $package.Sha) {
        throw ('SHA-256 incorrecto: ' + $package.Name)
    }
    Expand-Archive -LiteralPath $archive -DestinationPath (Join-Path $ToolsDirectory $package.Name) -Force
}
if (Test-Path -LiteralPath (Join-Path $OutputDirectory 'manifest.json')) {
    throw 'Ya existe una exportacion. Usa otro OutputDirectory para conservarla.'
}
New-Item -ItemType Directory -Path $OutputDirectory -Force | Out-Null
$original = Get-Item -LiteralPath $Source
$sourceHash = (Get-FileHash -LiteralPath $Source -Algorithm SHA256).Hash.ToLowerInvariant()
$copy = Join-Path $OutputDirectory 'copia-trabajo.fdb'
Copy-Item -LiteralPath $Source -Destination $copy
if ((Get-FileHash -LiteralPath $copy -Algorithm SHA256).Hash.ToLowerInvariant() -ne $sourceHash) {
    throw 'La copia no coincide con el original. Obtiene un respaldo consistente del PDV.'
}

$engine = Join-Path $ToolsDirectory 'firebird-x86'
$env:FIREBIRD = $engine
Add-Type -TypeDefinition 'using System; using System.Runtime.InteropServices; public class PdvNative { [DllImport("kernel32.dll", CharSet=CharSet.Unicode)] public static extern bool SetDllDirectory(string path); }'
[void][PdvNative]::SetDllDirectory($engine)
Add-Type -Path (Join-Path $ToolsDirectory 'provider/lib/net452/FirebirdSql.Data.FirebirdClient.dll')
$builder = New-Object FirebirdSql.Data.FirebirdClient.FbConnectionStringBuilder
$builder.Database = [string]$copy
$builder.ClientLibrary = [string](Join-Path $engine 'fbembed.dll')
$builder.ServerType = 1
$builder.UserID = 'SYSDBA'
$builder.Charset = $Charset
$builder.Pooling = $false
$connection = New-Object FirebirdSql.Data.FirebirdClient.FbConnection($builder.ConnectionString)
$utf8 = New-Object Text.UTF8Encoding($false)
$files = [ordered]@{}
function Save-Rows([string]$Name, [string]$Query) {
    $command = $connection.CreateCommand()
    $command.Transaction = $transaction
    $command.CommandText = $Query
    $reader = $command.ExecuteReader()
    $rows = New-Object 'System.Collections.Generic.List[object]'
    try {
        while ($reader.Read()) {
            $row = [ordered]@{}
            for ($i=0; $i -lt $reader.FieldCount; $i++) {
                $value = if ($reader.IsDBNull($i)) { $null } else { $reader.GetValue($i) }
                if ($value -is [DateTime]) { $value = $value.ToString('yyyy-MM-dd HH:mm:ss.fff', [Globalization.CultureInfo]::InvariantCulture) }
                if ($value -is [byte[]]) { $value = [Convert]::ToBase64String($value) }
                $row[$reader.GetName($i)] = $value
            }
            $rows.Add([PSCustomObject]$row)
        }
    } finally { $reader.Dispose(); $command.Dispose() }
    $path = Join-Path $OutputDirectory ($Name + '.json')
    [IO.File]::WriteAllText($path, (ConvertTo-Json -InputObject @($rows.ToArray()) -Depth 12), $utf8)
    $files[$Name + '.json'] = (Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash.ToLowerInvariant()
}
try {
    $connection.Open()
    $transaction = $connection.BeginTransaction([Data.IsolationLevel]::Serializable)
    $command = $connection.CreateCommand()
    $command.Transaction = $transaction
    $command.CommandText = 'SELECT TRIM(RDB$RELATION_NAME) FROM RDB$RELATIONS WHERE COALESCE(RDB$SYSTEM_FLAG, 0) = 0 AND RDB$VIEW_BLR IS NULL ORDER BY RDB$RELATION_NAME'
    $reader = $command.ExecuteReader()
    $tables = @()
    while ($reader.Read()) { $tables += $reader.GetString(0) }
    $reader.Dispose()
    $counts = [ordered]@{}
    foreach ($table in $tables) {
        $command.CommandText = 'SELECT COUNT(*) FROM "' + $table.Replace('"','""') + '"'
        $counts[$table] = [int]$command.ExecuteScalar()
    }
    $command.Dispose()
    Save-Rows 'schema' 'SELECT TRIM(r.RDB$RELATION_NAME) AS TABLE_NAME, TRIM(r.RDB$FIELD_NAME) AS FIELD_NAME, f.RDB$FIELD_TYPE AS FIELD_TYPE, f.RDB$FIELD_SCALE AS FIELD_SCALE, f.RDB$FIELD_LENGTH AS FIELD_LENGTH, TRIM(cs.RDB$CHARACTER_SET_NAME) AS CHARSET FROM RDB$RELATION_FIELDS r JOIN RDB$FIELDS f ON f.RDB$FIELD_NAME = r.RDB$FIELD_SOURCE JOIN RDB$RELATIONS t ON t.RDB$RELATION_NAME = r.RDB$RELATION_NAME LEFT JOIN RDB$CHARACTER_SETS cs ON cs.RDB$CHARACTER_SET_ID = f.RDB$CHARACTER_SET_ID WHERE COALESCE(t.RDB$SYSTEM_FLAG, 0) = 0 ORDER BY r.RDB$RELATION_NAME, r.RDB$FIELD_POSITION'
    # Archivar datos de negocio; no exportar claves de usuarios ni configuraciones.
    foreach ($table in @('PRODUCTOS','DEPARTAMENTOS','DEPTS','PRODUCTOS_BASE','HISTORIAL_INVENTARIO','FACTURACION_CLIENTES','CLIENTES','ABONOS','PROV','VENTAS','VENTATICKETS','VENTATICKETS_ARTICULOS','MOVIMIENTOS','OPERACIONES','MEDIDAS','IMPUESTOS','PROMOCIONES_POR_CANTIDAD')) {
        if ($tables -contains $table) { Save-Rows $table ('SELECT * FROM "' + $table + '"') }
    }
    $transaction.Commit()
    if ((Get-FileHash -LiteralPath $Source -Algorithm SHA256).Hash.ToLowerInvariant() -ne $sourceHash) {
        throw 'El original cambio durante la exportacion. Repite con un respaldo consistente.'
    }
    $manifest = [ordered]@{
        source_sha256=$sourceHash; source_bytes=$original.Length
        source_modified_at=$original.LastWriteTime.ToString('yyyy-MM-dd HH:mm:ss')
        exported_at=[DateTime]::UtcNow.ToString('o'); engine=$connection.ServerVersion
        charset=$Charset; counts=$counts; files=$files
    }
    [IO.File]::WriteAllText((Join-Path $OutputDirectory 'manifest.json'), (ConvertTo-Json -InputObject $manifest -Depth 12), $utf8)
    Write-Output ('Exportacion completa: ' + $OutputDirectory)
    $counts.GetEnumerator() | Format-Table Key,Value -AutoSize
} finally { $connection.Dispose() }
