<?php
namespace Diapositive\Controllers;

use Diapositive\Foundations\Controller;
use Upload\Storage\FileSystem;
use Upload\File;
use Upload\Validation;
use Intervention\Image\ImageManager;

class SlideShowController extends Controller {

    public function create() {
        $_SESSION['slideshow_create_uuid'] = uniqid();

        $this->render('slideshow/create.html');
    }

    public function upload() {
        $storage_path = STORAGE_ROOT.'/slideshows/'.$_SESSION['slideshow_create_uuid'];

        if (is_dir($storage_path) === false) {
            mkdir($storage_path, 0777, true);
        }

        $file_system = new FileSystem($storage_path);

        $file = new File('file', $file_system);
        $file->setName(date("YmdHis").'-'.time().'-'.$file->getMd5());
        $file->addValidations([
            new Validation\Mimetype(['image/png', 'image/jpeg', 'image/jpg', 'image/gif']),
            new Validation\Size('5M')
        ]);

        try {
            $file->upload();

            // Covert to jpg format
            if ($file->getExtension() != "jpg") {
                $image_file_path = $storage_path.'/'.$file->getNameWithExtension();

                $image_maanger = new ImageManager();
                $image_maanger->make($image_file_path)->save($storage_path.'/'.$file->getName().'.jpg', 100);

                unlink($image_file_path);
            }

            echo json_encode(['status' => 'ok']);
        }catch(\Exception $e) {
            $this->app->halt(500, json_encode($file->getErrors()));
        }
    }

}
