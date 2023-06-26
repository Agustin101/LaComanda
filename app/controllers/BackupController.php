<?php

use League\Csv\Reader;
use League\Csv\Statement;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;

require_once "./Repositorios/ProductoRepositorio.php";
require_once "./models/Producto.php";

class BackupController
{
    public function DescargarBackup(IRequest $req, IResponse $res, $args)
    {
        $productos = ProductoRepositorio::ObtenerProductos();
        $this->CrearCsv($productos);
        $res->getBody()->write("El backup se genero correctamente.");
        return $res
            ->withHeader('Content-Type', 'application/json')->withStatus(200);

    }

    public function CargarBackup(IRequest $req, IResponse $res, $args)
    {
        $files = $req->getUploadedFiles();
        $uploadedFile = $files['csv'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $tempFilePath = __DIR__ . uniqid() . '.csv';
            $uploadedFile->moveTo($tempFilePath);

            $csv = Reader::createFromPath($tempFilePath, 'r');
            $csv->setHeaderOffset(0);

            $stmt = (new Statement())->offset(0);
            $records = $stmt->process($csv);

            $productos = array();
            foreach ($records as $record) {
                $producto = new Producto();
                $producto->productoCodigo = $record["CODIGO_PRODUCTO"];
                $producto->descripcion = $record["DESCRIPCION"];
                $producto->sectorCodigo = $record["CODIGO_SECTOR"];
                $producto->precio = $record["PRECIO"];
                $producto->fechaAlta = $record["FH_ALTA"];
                if ($record["FH_MODIF"] != "") {
                    $producto->fechaModificacion = $record["FH_MODIF"];
                }

                if ($record["FH_BAJA"] != "") {
                    $producto->fechaBaja = $record["FH_BAJA"];
                }

                $producto->activo = $record["ACTIVO"];
                array_push($productos, $producto);
            }

            try {
                foreach ($productos as $producto) {
                    ProductoRepositorio::AgregarProductoBackup($producto);
                }
            } catch (Exception $ex) {
                echo $ex;
                return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            finally{
                unlink($tempFilePath);
            }

            $res->getBody()->write(json_encode(array("mensaje" => "Backup cargado correctamente")));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        $res->getBody()->write(json_encode(array("mensaje" => "Hubo un error al cargar el archivo csv.")));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    private function LeerCsv($file)
    {

    }

    private function CrearCsv(array $productos)
    {
        $destino = "C:\\xampp\\htdocs\\LaComanda\\app\\backup\\productos.csv";
        $f = fopen($destino, 'w');

        $props = get_object_vars($productos[0]);
        $encabezado = array_keys($props);
        fputcsv($f, $encabezado);

        foreach ($productos as $producto) {
            $valores = array_values(get_object_vars($producto));
            fputcsv($f, $valores);
        }

        fclose($f);
    }
}
