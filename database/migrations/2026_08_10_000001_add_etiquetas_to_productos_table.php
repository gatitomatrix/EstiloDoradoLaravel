<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('productos', 'etiquetas')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('etiquetas', 500)->nullable()->after('descripcion');
            });
        }

        $map = $this->tagsMap();
        foreach ($map as $id => $tags) {
            DB::table('productos')->where('id_producto', $id)->update(['etiquetas' => $tags]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'etiquetas')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('etiquetas');
            });
        }
    }

    /** @return array<int, string> */
    private function tagsMap(): array
    {
        return [
            1 => 'flores,regalo,arreglo,cumpleaños,detalle,detalles,dulces,fiesta,globos,personalizado',
            2 => 'peluche,flores,juguete,regalo,arreglo,detalle,dulces,infantil,personalizado,stich,rosa',
            3 => 'flores,regalo,arreglo,azul,detalle,enamorados,pareja,personalizado,romance',
            4 => 'flores,regalo,arreglo,detalle,personalizado',
            5 => 'peluche,flores,juguete,regalo,arreglo,detalle,dulces,gatito,infantil,personalizado,rosa',
            6 => 'peluche,juguete,regalo,adulto,cerveza,detalle,dulces,infantil,osito,personalizado',
            7 => 'flores,juguete,regalo,arreglo,auto,azul,detalle,hotwheels,personalizado',
            8 => 'flores,regalo,adulto,arreglo,azul,cerveza,detalle,personalizado',
            9 => 'flores,regalo,arreglo,detalle,personalizado,rosa',
            10 => 'regalo,detalle,dulces,fiesta,globos,personalizado',
            11 => 'peluche,juguete,regalo,detalle,fiesta,globos,infantil,personalizado',
            12 => 'peluche,juguete,regalo,detalle,dulces,foto,infantil,pareja,personalizado,romance',
            13 => 'peluche,caja,cajita,juguete,regalo,adulto,cerveza,detalle,dulces,fiesta,globos,personalizado',
            14 => 'caja,cajita,regalo,alianza,deporte,detalle,futbol,personalizado',
            15 => 'peluche,caja,cajita,juguete,regalo,azul,detalle,infantil,osito,personalizado',
            16 => 'peluche,juguete,regalo,azul,detalle,infantil,personalizado',
            17 => 'peluche,flores,bolso,juguete,regalo,accesorio,arreglo,detalle,infantil,moda,personalizado',
            18 => 'regalo,azul,detalle,personalizado',
            19 => 'peluche,caja,cajita,juguete,regalo,auto,detalle,dulces,hotwheels,infantil,personalizado',
            20 => 'juguete,regalo,auto,azul,detalle,hotwheels,personalizado',
            21 => 'juguete,regalo,auto,detalle,hotwheels,personalizado',
            22 => 'peluche,caja,cajita,juguete,regalo,adulto,cerdita,cerveza,detalle,dulces,fiesta,personalizado',
            23 => 'caja,cajita,regalo,adulto,cerveza,detalle,dulces,fiesta,globos,personalizado',
            24 => 'regalo,adulto,alianza,cerveza,deporte,detalle,dulces,futbol,personalizado',
            25 => 'caja,cajita,regalo,detalle,dulces,personalizado',
            26 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            27 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            28 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            29 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            30 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            31 => 'flores,regalo,arreglo,detalle,florales,foto,personalizado',
            32 => 'flores,regalo,arreglo,detalle,fiesta,florales,globos,personalizado',
            33 => 'flores,regalo,arreglo,detalle,fiesta,florales,globos,personalizado',
            34 => 'peluche,flores,caja,cajita,juguete,regalo,arreglo,detalle,dulces,fiesta,florales,personalizado',
            35 => 'flores,regalo,arreglo,detalle,fiesta,florales,globos,personalizado',
            36 => 'peluche,flores,caja,cajita,juguete,regalo,arreglo,detalle,dulces,fiesta,florales,personalizado',
            37 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            38 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            39 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            40 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            41 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            42 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            43 => 'flores,regalo,arreglo,detalle,florales,personalizado',
            44 => 'cartel,regalo,carteles,cumpleaños,fiesta,mensaje,personalizado',
            45 => 'cartel,caja,cajita,regalo,carteles,detalle,dulces,foto,mensaje,personalizado',
            46 => 'cartel,caja,cajita,regalo,carteles,detalle,mensaje,personalizado',
            47 => 'flores,cartel,caja,cajita,regalo,arreglo,carteles,detalle,dulces,mensaje,personalizado',
            48 => 'flores,cartel,caja,cajita,regalo,arreglo,carteles,detalle,dulces,mensaje,personalizado',
            49 => 'cartel,caja,cajita,juguete,regalo,auto,carteles,detalle,dulces,hotwheels,mensaje,personalizado',
            50 => 'cartel,caja,cajita,juguete,regalo,auto,carteles,detalle,dulces,hotwheels,mensaje,personalizado',
            51 => 'peluche,flores,cartel,caja,cajita,juguete,regalo,arreglo,carteles,detalle,dulces,personalizado',
            52 => 'peluche,flores,cartel,caja,cajita,juguete,regalo,arreglo,carteles,detalle,dulces,personalizado',
            53 => 'peluche,cartel,caja,cajita,juguete,regalo,adulto,carteles,cerveza,detalle,dulces,personalizado',
            54 => 'peluche,cartel,caja,cajita,juguete,regalo,adulto,carteles,cerveza,detalle,dulces,personalizado',
            55 => 'peluche,cartel,caja,cajita,juguete,regalo,carteles,detalle,dulces,infantil,mensaje,personalizado',
            56 => 'peluche,flores,cartel,caja,cajita,juguete,regalo,arreglo,carteles,detalle,dulces,personalizado',
            57 => 'cartel,regalo,carteles,dulces,fiesta,globos,mensaje,personalizado,rosa',
            58 => 'cartel,caja,cajita,regalo,carteles,detalle,dulces,fiesta,globos,mensaje,personalizado',
            59 => 'cartel,caja,cajita,regalo,carteles,detalle,dulces,fiesta,globos,mensaje,personalizado',
            60 => 'cartel,caja,cajita,regalo,adulto,azul,carteles,cerveza,detalle,dulces,fiesta,globos',
            61 => 'cartel,regalo,carteles,dulces,fiesta,globos,mensaje,personalizado',
            62 => 'bolso,accesorio,moda,verde,regalo',
            63 => 'bolso,accesorio,moda,militar,verde,regalo',
            64 => 'bolso,accesorio,cuero,moda,regalo',
            65 => 'bolso,accesorio,bolsa,crema,cuero,moda,regalo',
            66 => 'bolso,accesorio,bear,moda,piton,regalo',
            67 => 'bolso,accesorio,moda,piton,regalo',
            68 => 'bolso,accesorio,mochila,moda,piton,regalo',
            69 => 'bolso,accesorio,mano,moda,piton,regalo',
            70 => 'caja,cajita,regalo,detalle,dulces,personalizado',
            71 => 'perfume,fragancia,perfumeria,dance,regalo',
            72 => 'caja,cajita,regalo,circular,detalle,dulces,personalizado',
            73 => 'peluche,flores,juguete,regalo,arreglo,cerdita,cerdito,detalle,infantil,rosa,tiburon',
            74 => 'peluche,caja,cajita,juguete,regalo,detalle,fiesta,globos,infantil,rosa',
            75 => 'caja,cajita,juguete,regalo,auto,detalle,hotwheels,negra,sorpresa',
            76 => 'peluche,caja,cajita,juguete,regalo,circular,detalle,infantil,rosa,variados',
            77 => 'peluche,juguete,regalo,infantil,stich,variados',
            78 => 'peluche,juguete,regalo,infantil,rosa,stich,variados',
            79 => 'peluche,caja,cajita,juguete,regalo,detalle,dulces,fiesta,globos,infantil,pinguino,variados',
            80 => 'reloj,regalo,accesorio,clasicos,variados',
            81 => 'pulsera,regalo,accesorio,metalizadas,variados',
            82 => 'pulsera,regalo,accesorio,negras,variados',
            83 => 'billetera,regalo,accesorio,caballero,chicago,marca,variados',
            84 => 'billetera,regalo,accesorio,caballero,marca,renzo,costa,variados',
            85 => 'billetera,regalo,accesorio,caballero,chicago,dorado,marca,variados',
            86 => 'billetera,regalo,accesorio,caballero,chicago,clasica,marca,variados',
            87 => 'billetera,regalo,accesorio,caballero,marca,puma,variados',
            88 => 'peluche,flores,juguete,regalo,arreglo,cerdita,detalle,infantil,rosa,tiburon,variados',
            89 => 'caja,cajita,regalo,detalle,dulcera,dulces,fiesta,globos,personalizado,variados',
            90 => 'caja,cajita,juguete,regalo,auto,detalle,dulces,hotwheels,variados',
        ];
    }
};
