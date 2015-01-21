<?php
namespace Diapositive\Controllers;

use Upload\Storage\FileSystem;
use Upload\File;
use Upload\Validation;
use Intervention\Image\ImageManager;
use Resque;
use Diapositive\Foundations\Controller;
use Diapositive\Foundations\Model;
use Diapositive\Helpers\FileSystem as FileSystemHelper;
use Diapositive\Models;

class SlideShowController extends Controller {

    public function index() {
        $jobs = Models\Job::where('user_id', $_SESSION['user']['id'])->findMany();

        $this->render('slideshow/index.html', compact('jobs'));
    }

    public function create() {
        if ($this->request->isPost() === true) {
            $storage_path = STORAGE_ROOT.'/slideshows/'.$_SESSION['slideshow_uuid'];

            $valid_type        = 'error';
            $valid_message     = '';
            $valid_redirect_to = 'slideshow.create';

            if (empty($_SESSION['slideshow_uuid']) === true) {
                $valid_message = 'Please make sure entered from create page';
            }else if (is_dir($storage_path) === false) {
                $valid_message = 'Please upload images first';
            }else if (FileSystemHelper::countTotalFiles($storage_path) < 2) {
                FileSystemHelper::removeDirectory($storage_path);

                $valid_message = 'Please upload more than 2 images';
            }else{
                $job_token = Resque::enqueue('default', 'MakeVideoJob', [
                    'user_id'        => $_SESSION['user']['id'],
                    'slideshow_uuid' => $_SESSION['slideshow_uuid'],
                ], true);

                Model::factory('Job')->create([
                    'user_id'        => $_SESSION['user']['id'],
                    'job_token'      => $job_token,
                    'slideshow_uuid' => $_SESSION['slideshow_uuid'],
                    'status'         => "waiting",
                    'create_at'      => time()
                ])->save();

                $valid_type        = 'success';
                $valid_message     = 'The generate request has been submitted, Please wait a moment';
                $valid_redirect_to = 'slideshow.index';
            }

            $this->flash($valid_type, $valid_message);
            $this->redirectTo($valid_redirect_to);
        }else{
            $_SESSION['slideshow_uuid'] = uniqid();

            $this->render('slideshow/create.html');
        }
    }

    public function upload() {
        $storage_path = STORAGE_ROOT.'/slideshows/'.$_SESSION['slideshow_uuid'];

        if (is_dir($storage_path) === false) {
            mkdir($storage_path, 0777, true);
        }

        $file_system = new FileSystem($storage_path);

        $file = new File('file', $file_system);
        $file->setName(sprintf("%05d", FileSystemHelper::countTotalFiles($storage_path)).'-'.$file->getMd5());
        $file->addValidations([
            new Validation\Mimetype(['image/png', 'image/jpeg', 'image/jpg', 'image/gif']),
            new Validation\Size('5M')
        ]);

        try {
            $file->upload();

            // Covert to jpg format and resize to same width and height
            $image_file_path = $storage_path.'/'.$file->getNameWithExtension();

            $image_manager = new ImageManager();
            $image_manager->canvas(500, 500, '#000000')->insert(
                $image_manager->make($image_file_path)->resize(500, null, function($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }),
                'center'
            )->save($storage_path.'/'.$file->getName().'.jpg', 100);

            // Delete the file extension is not .jpg file
            if ($file->getExtension() != "jpg") {
                unlink($image_file_path);
            }

            echo json_encode(['status' => 'ok']);
        }catch(\Exception $e) {
            $this->app->halt(500, json_encode($file->getErrors()));
        }
    }

}
