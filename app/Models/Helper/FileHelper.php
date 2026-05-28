<?php

namespace App\Models\Helper;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


use Intervention\Image\Drivers\AbstractDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileHelper
{


    public static function getUploadPath(){
        // For cpanel
        return base_path() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        // For artisan / VPS
        // return base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    }


    public static function uploadPath($image, $deleting = false)
    {
        if ($image === Config::get('constants.media.DEFAULT_IMAGE') && $deleting) {
            $image = time() . '-' . mt_rand(1, 9);
        }

        return self::getUploadPath() . $image;
    }



    public static function imgSrcUrl()
    {
        if (config('env.media.STORAGE') == config('env.media.LOCAL')) {
            return config('env.url.APP_URL') . '/uploads/';

        } else if (config('env.media.STORAGE') == config('env.media.GCS')) {
            return config('env.media.CDN_URL') . 'uploads/';

        } else if (config('env.media.STORAGE') == config('env.media.S3')) {
            if (config('env.media.CDN_URL')) {
                return config('env.media.CDN_URL');
            }
            $endpoint = config('filesystems.disks.s3.endpoint');
            $bucket = config('filesystems.disks.s3.bucket');
            $usePathStyle = config('filesystems.disks.s3.use_path_style_endpoint');
            return $usePathStyle
                ? rtrim($endpoint, '/') . '/' . $bucket . '/'
                : str_replace('://', '://' . $bucket . '.', rtrim($endpoint, '/')) . '/';

        } else if (config('env.media.STORAGE') == config('env.media.URL')) {
            return config('env.media.CDN_URL');
        }
    }




    public static function imageLink($image)
    {
        if ($image == 'null' || $image == '' || is_null($image)) {
            $image = Config::get('constants.media.DEFAULT_IMAGE');
        }

        return self::imgSrcUrl() . $image;
    }


    public static function imageFullUrl($image)
    {
        if ($image == 'null' || $image == '' || is_null($image)) {
            $image = Config::get('constants.media.DEFAULT_IMAGE');
        }

        if (config('env.media.STORAGE') == config('env.media.LOCAL')) {
            return env('APP_URL', config('env.url.APP_URL')) . '/uploads/' . $image;

        } else if (config('env.media.STORAGE') == config('env.media.GCS')) {
            return config('env.media.CDN_URL') . 'uploads/' . $image;

        } else if (config('env.media.STORAGE') == config('env.media.S3')) {
            return config('env.media.CDN_URL') ? config('env.media.CDN_URL') . $image : Storage::disk('s3')->url($image);
        }


        return false;
    }


    public static function imageToBase64($image, $default = true)
    {

        try {

            if ($default && ($image == 'null' || $image == '' || is_null($image))) {
                $image = Config::get('constants.media.DEFAULT_IMAGE');
            }

            $content = '';

            if ($image) {

                if (config('env.media.STORAGE') == config('env.media.LOCAL')) {
                    //$path = Storage::disk('public')->getAdapter()->applyPathPrefix($image);
                    $path = base_path('uploads/' . $image);
                   // $path = base_path('public/uploads/' . $image);

                    if (file_exists($path)) {
                        $content = base64_encode(file_get_contents($path));
                    }


                } else if (config('env.media.STORAGE') == config('env.media.GCS')) {
                    $path = config('env.media.CDN_URL') . 'uploads/' . $image;

                    $content = base64_encode(file_get_contents($path));

                } else if (config('env.media.STORAGE') == config('env.media.S3')) {
                    if (Storage::disk('s3')->exists($image)) {
                        $content = base64_encode(Storage::disk('s3')->get($image));
                    }
                }
            }

            return $content;



        } catch (\Exception $ex) {
            return response()->json(Validation::error(null, $ex->getMessage()));
        }


    }


    public static function deleteFile($image)
    {
        try {

            if (config('env.media.STORAGE') == config('env.media.LOCAL')) {
                return self::deleteFileLocal($image);

            } else if (config('env.media.STORAGE') == config('env.media.GCS')) {
                return self::deleteFileGcs($image);

            } else if (config('env.media.STORAGE') == config('env.media.S3')) {
                return self::deleteFileS3($image);
            }

            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function readAllFileGcs()
    {
        try {

            $storage = new StorageClient([
                'keyFilePath' => base_path() . DIRECTORY_SEPARATOR . config('googlecloud.gc_key_file'),
            ]);


            $storageBucketName = config('googlecloud.storage_bucket');
            $bucket = $storage->bucket($storageBucketName);

            $images = [];

            foreach ($bucket->objects() as $object) {
                if(Utils::startsWith($object->name(), 'uploads/')){
                    if($object->name() !== 'uploads/'){
                        array_push($images, str_replace('uploads/', '', $object->name()));
                    }
                }
            }
            return $images;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return true;
    }


    public static function deleteFileGcs($image)
    {
        try {

            $storage = new StorageClient([
                'keyFilePath' => base_path() . DIRECTORY_SEPARATOR . config('googlecloud.gc_key_file'),
            ]);


            $storageBucketName = config('googlecloud.storage_bucket');
            $bucket = $storage->bucket($storageBucketName);
            $object = $bucket->object(config('googlecloud.path_prefix') . $image);


            if ($object->exists()) {

                $object->delete();
                $thumbObject = $bucket->object(config('googlecloud.path_prefix') . env('THUMB_PREFIX') . $image);

                if ($thumbObject->exists()) {
                    $thumbObject->delete();
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return true;
    }


    public static function deleteFileLocal($image)
    {
        try {

            $file_path = $image ? FileHelper::uploadPath($image, true) : null;

            if (file_exists($file_path)) {
                unlink($file_path);

                $thumb_file_path = $image ?
                    FileHelper::uploadPath(env('THUMB_PREFIX') . $image, true) : null;

                if (file_exists($thumb_file_path)) {
                    unlink($thumb_file_path);
                }
            }
            return true;


        } catch (\Exception $e) {
            throw $e;
        }

    }


    public static function uploadImage($file, $prefix, $thumb = true)
    {
        try {

            if (config('env.media.STORAGE') == config('env.media.LOCAL')) {
                return self::uploadToLocal($file, $prefix, $thumb);

            } else if (config('env.media.STORAGE') == config('env.media.GCS')) {
                return self::uploadToGcs($file, $prefix, $thumb);

            } else if (config('env.media.STORAGE') == config('env.media.S3')) {
                return self::uploadToS3($file, $prefix, $thumb);

            } else if (config('env.media.STORAGE') == config('env.media.URL')) {
                $data['name'] = $file;

                return $data;
            }

            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function uploadToGcs($file, $prefix, $thumb = true)
    {

        try {

            $storage = new StorageClient([
                'keyFilePath' => base_path() . DIRECTORY_SEPARATOR . config('googlecloud.gc_key_file'),
            ]);

            $storageBucketName = config('googlecloud.storage_bucket');
            $bucket = $storage->bucket($storageBucketName);


            $image_path = $file->getRealPath();

            $extension = $file->getClientOriginalExtension();
            $filename = $prefix . '-' . time() . '-' . mt_rand(1, 9) . '.' . $extension;

            $fileSource = fopen($image_path, 'r');
            $googleCloudStoragePath = config('googlecloud.path_prefix') . $filename;


            /* Upload a file to the bucket.
            Using Predefined ACLs to manage object permissions, you may
            upload a file and give read access to anyone with the URL.*/
            $bucket->upload($fileSource, [
                // 'predefinedAcl' => 'publicRead',
                'name' => $googleCloudStoragePath
            ]);

            if ($thumb) {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);
                $thumbImg = $image->resize(320, 320, function ($constraint) {
                    $constraint->aspectRatio();
                });


                $googleCloudStorageThumbPath = config('googlecloud.path_prefix') . env('THUMB_PREFIX') . $filename;

                $bucket->upload($thumbImg->stream(), [
                    'name' => $googleCloudStorageThumbPath
                ]);
            }


            $data['name'] = $filename;

            return $data;


        } catch (\Exception $e) {
            throw $e;
        }




    }

    public static function uploadToLocal($file, $prefix, $thumb = true)
    {

        try{
            $extension = $file->getClientOriginalExtension();
            $filename = $prefix . '-' . time() . '-' . mt_rand(1, 9) . '.' . $extension;

            if ($thumb) {
                self::generateThumbLocal($file, $filename);
            }

            Storage::disk('public')->put($filename, File::get($file));

            $data['name'] = $filename;

            return $data;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }


    public static function generateThumbLocal($file, $filename)
    {
        try{
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            $image = $image->resize(320, 320, function ($constraint) {
                $constraint->aspectRatio();
            });
            //return $image->save(base_path('public/uploads/'.env('THUMB_PREFIX') . $filename));
            return $image->save(base_path('uploads/'.env('THUMB_PREFIX') . $filename));
        } catch (\Exception $ex) {
            throw $ex;
        }

    }

    public static function deleteFileS3($image)
    {
        try {
            if (Storage::disk('s3')->exists($image)) {
                Storage::disk('s3')->delete($image);
            }

            $thumbName = env('THUMB_PREFIX') . $image;
            if (Storage::disk('s3')->exists($thumbName)) {
                Storage::disk('s3')->delete($thumbName);
            }
            return true;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return true;
    }

    public static function readAllFileS3()
    {
        try {
            return Storage::disk('s3')->files();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return [];
    }

    public static function uploadToS3($file, $prefix, $thumb = true)
    {
        try {
            $extension = $file->getClientOriginalExtension();
            $filename = $prefix . '-' . time() . '-' . mt_rand(1, 9) . '.' . $extension;

            if ($thumb) {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);
                $thumbImg = $image->resize(320, 320, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $thumbName = env('THUMB_PREFIX') . $filename;
                Storage::disk('s3')->put($thumbName, $thumbImg->encode()->toString(), 'public');
            }

            Storage::disk('s3')->put($filename, File::get($file), 'public');

            $data['name'] = $filename;
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
