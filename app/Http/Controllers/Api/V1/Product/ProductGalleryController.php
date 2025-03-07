<?php

namespace App\Http\Controllers\Api\V1\Product;
use App\Features\Ftp\Domain\Repositories\FtpRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductGalleryController extends Controller
{

    protected $ftpRepository;

    public function __construct(FtpRepositoryInterface $ftpRepository)
    {
        $this->ftpRepository = $ftpRepository;
    }

    public function uploadImage(Request $request, $productId)
    {
        Log::info('uploadImage', ['productId' => $productId]);

        // Validar que el producto existe
        $product = Product::find($productId);
        if (!$product) {
            Log::warning('Producto no encontrado', ['productId' => $productId]);
            return response()->json(['error' => 'El producto no existe'], 404);
        }

        // Validaciones de imagen
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048', // Formatos permitidos y límite de 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            // Intentar subir la imagen al FTP
            $filePath = $this->ftpRepository->saveGalleryImage($productId, $request->file('image'));

            if (!$filePath) {
                Log::error('Fallo al subir la imagen al FTP');
                return response()->json(['error' => 'Error al subir la imagen al servidor'], 500);
            }

            // Guardar la ruta en la base de datos
            $gallery = Gallery::create([
                'image_url'  => $filePath,
                'product_id' => $productId,
                'active'     => true
            ]);

            Log::info('Imagen guardada en la base de datos', ['gallery' => $gallery]);

            return response()->json([
                'message' => '✅ Imagen subida y guardada correctamente',
                'gallery' => $gallery
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error en la base de datos al guardar la imagen', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al guardar la imagen en la base de datos'], 500);
        } catch (\Exception $e) {
            Log::error('Error inesperado subiendo imagen', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error inesperado al subir la imagen'], 500);
        }
    }

    public function deleteImage($imageId)
    {
        try {
            $image = Gallery::findOrFail($imageId);

            // Eliminar la imagen del servidor FTP
            $this->ftpRepository->deleteFileFromFtp($image->getRawOriginal('image_url'));

            // Eliminar el registro de la base de datos
            $image->delete();

            return response()->json(['success' => true, 'message' => 'Imagen eliminada correctamente']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar la imagen:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['success' => false, 'error' => 'No se pudo eliminar la imagen'], 500);
        }
    }

}
