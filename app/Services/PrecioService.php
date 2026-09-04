<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Promocion;

class PrecioService
{
    private mixed $campana = false;

    public function campanaActiva(): ?Promocion
    {
        if ($this->campana !== false) {
            return $this->campana;
        }
        $this->campana = null;
        try {
            $row = Promocion::query()->where('activo', true)->orderByDesc('id')->first();
            if ($row && $this->vigente($row->fecha_inicio, $row->fecha_fin) && (float) $row->porcentaje > 0) {
                $this->campana = $row;
            }
        } catch (\Throwable $e) {
            $this->campana = null;
        }

        return $this->campana;
    }

    public function pctProducto(Producto $p): float
    {
        try {
            $pct = (float) ($p->descuento_pct ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
        if ($pct <= 0) {
            return 0;
        }
        $hasta = $p->oferta_hasta ?? null;
        if ($hasta && now('America/Lima')->toDateString() > substr((string) $hasta, 0, 10)) {
            return 0;
        }

        return min(90, $pct);
    }

    public function pctEfectivo(Producto $p): float
    {
        $prod = $this->pctProducto($p);
        $camp = (float) ($this->campanaActiva()?->porcentaje ?? 0);

        return min(90, max($prod, $camp));
    }

    public function precioFinal(Producto $p): float
    {
        $lista = (float) $p->precio_venta;
        $pct = $this->pctEfectivo($p);
        if ($pct <= 0) {
            return round($lista, 2);
        }

        return round($lista * (1 - $pct / 100), 2);
    }

    private function vigente(mixed $ini, mixed $fin): bool
    {
        $hoy = now('America/Lima')->toDateString();
        $a = $ini ? substr((string) $ini, 0, 10) : null;
        $b = $fin ? substr((string) $fin, 0, 10) : null;
        if ($a && $hoy < $a) {
            return false;
        }
        if ($b && $hoy > $b) {
            return false;
        }

        return true;
    }
}
