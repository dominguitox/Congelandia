import test from 'node:test';
import assert from 'node:assert/strict';
import { calcularCobro } from '../../resources/js/cobro.js';

test('calcula el vuelto de un billete sin aumentar el total aplicado', () => {
    const resumen = calcularCobro(6500, [{idMetodo:1,monto:6500,esEfectivo:true}], 10000);
    assert.equal(resumen.valido, true);
    assert.equal(resumen.asignado, 6500);
    assert.equal(resumen.vuelto, 3500);
});
test('el vuelto mixto descuenta exclusivamente la parte en efectivo', () => {
    const resumen = calcularCobro(6500, [{idMetodo:1,monto:2500,esEfectivo:true},{idMetodo:2,monto:4000,esEfectivo:false}], 5000);
    assert.equal(resumen.valido, true);
    assert.equal(resumen.vuelto, 2500);
});
test('un pago con tarjeta no necesita efectivo', () => {
    const resumen = calcularCobro(6500, [{idMetodo:2,monto:6500,esEfectivo:false}], 0);
    assert.equal(resumen.valido, true);
    assert.equal(resumen.vuelto, 0);
});
for (const [name,pagos,recibido] of [
    ['importe insuficiente',[{idMetodo:1,monto:6000,esEfectivo:true}],10000],
    ['importe excesivo',[{idMetodo:1,monto:7000,esEfectivo:true}],10000],
    ['efectivo insuficiente',[{idMetodo:1,monto:6500,esEfectivo:true}],5000],
    ['fracciones de peso',[{idMetodo:1,monto:6500.5,esEfectivo:true}],10000],
    ['metodos repetidos',[{idMetodo:1,monto:2500,esEfectivo:true},{idMetodo:1,monto:4000,esEfectivo:true}],10000],
]) test('impide confirmar con '+name, () => assert.equal(calcularCobro(6500,pagos,recibido).valido,false));
