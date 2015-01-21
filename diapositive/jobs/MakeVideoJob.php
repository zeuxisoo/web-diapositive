<?php
use Diapositive\Models;
use mikehaertl\shellcommand\Command;

class MakeVideoJob {

    public $job;

    public static $JOB_STATUS_CONVERTING = 'converting';
    public static $JOB_STATUS_FINISH     = 'finish';
    public static $JOB_STATUS_FAILED     = 'failed';

    public function perform() {
        $user_id        = $this->args['user_id'];
        $slideshow_uuid = $this->args['slideshow_uuid'];
        $storage_path   = STORAGE_ROOT.'/slideshows/'.$slideshow_uuid;

        // Slideshow options
        $fade_duration  = 0.7;
        $fade_st        = 5-0.7;
        $slide_duration = 5;
        $slide_format   = "-pix_fmt yuvj420p -c:v libx264 -preset slow";

        // Find slideshow object
        $this->jobs = Models\Job::where('user_id', $user_id)->where('slideshow_uuid', $slideshow_uuid)->findOne();

        // Mark status as converting
        $this->setStatus(self::$JOB_STATUS_CONVERTING);

        // Find all images end with .jpg, first and last image
        $images = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($storage_path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST,
                RecursiveIteratorIterator::CATCH_GET_CHILD
            ),
            '/^.+\.(jpg)$/i'
        );

        $all_images  = iterator_to_array($images, false);
        $head_image  = current($all_images);
        $tail_image  = end($all_images);

        // Make fade in effect on first image frame
        $command = new Command(sprintf(
            'ffmpeg -loop 1 -i "%s" -vf fade=in:st=0:d=%s -t %s %s -y "%s"',
            $storage_path.'/'.$head_image->getFilename(),
            $fade_duration,
            $slide_duration,
            $slide_format,
            $storage_path.'/'.$this->getVideoFrameName($head_image, 'head')
        ));
        $command->execute();

        // Make middle frame for each image
        foreach($all_images as $image) {
            $command = new Command(sprintf(
                'ffmpeg -loop 1 -i "%s" -t %s %s -y "%s"',
                $storage_path.'/'.$image->getFilename(),
                $slide_duration,
                $slide_format,
                $storage_path.'/'.$this->getVideoFrameName($image, 'middle')
            ));
            $command->execute();
        }

        // Make transition frame between each image but without last image
        for($i=0, $s=count($all_images) - 1; $i<$s; $i++) {
            $current_image = $all_images[$i];
            $next_image    = $all_images[$i+1];

            $command = new Command(sprintf(
                'ffmpeg -loop 1 -i "%s" -loop 1 -i "%s" -filter_complex "[1:v][0:v]blend=all_expr=\'A*(if(gte(T,%s),1,T/%s))+B*(1-(if(gte(T,%s),1,T/%s)))\'" -t %s %s -y "%s"',
                $storage_path.'/'.$current_image->getFilename(),
                $storage_path.'/'.$next_image->getFilename(),
                $fade_duration,
                $fade_duration,
                $fade_duration,
                $fade_duration,
                $fade_duration,
                $slide_format,
                $storage_path.'/'.$this->getVideoFrameName($current_image, 'transition')
            ));
            $command->execute();
        }

        // Make fade out effect on last image frame
        $command = new Command(sprintf(
            'ffmpeg -loop 1 -i "%s" -vf fade=out:st=%s:d=%s -t %s %s -y "%s"',
            $storage_path.'/'.$tail_image->getFilename(),
            $fade_st,
            $fade_duration,
            $slide_duration,
            $slide_format,
            $storage_path.'/'.$this->getVideoFrameName($tail_image, 'tail')
        ));
        $command->execute();

        // Concat all video frame to one mp4
        for($i=0, $s=count($all_images); $i<$s; $i++) {
            $image    = $all_images[$i];
            $filename = $image->getBasename(".".$image->getExtension());

            $template = 'file %s/%s';

            // first
            if ($i === 0) {
                $this->addToConcatFile($storage_path, $filename."-head.mp4");
                $this->addToConcatFile($storage_path, $filename."-middle.mp4");
                $this->addToConcatFile($storage_path, $filename."-transition.mp4");
            }

            // middle & transition
            if ($i > 0 && $i < $s - 1) {
                $this->addToConcatFile($storage_path, $filename."-middle.mp4");
                $this->addToConcatFile($storage_path, $filename."-transition.mp4");
            }

            // last
            if ($i === $s - 1) {
                $this->addToConcatFile($storage_path, $filename."-middle.mp4");
                $this->addToConcatFile($storage_path, $filename."-tail.mp4");
            }
        }

        $command = new Command(sprintf(
            'ffmpeg -f concat -i "%s" -c copy -y "%s"',
            $storage_path.'/concat.txt',
            $storage_path.'/concat.mp4'
        ));
        $command->execute();

        // Remove unused video frames
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($storage_path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST,
                RecursiveIteratorIterator::CATCH_GET_CHILD
            ),
            '/^.+\.(mp4|txt)$/i'
        );

        foreach($files as $full_path => $file) {
            if ($file->getFilename() !== "concat.mp4") {
                unlink($full_path);
            }
        }

        // Mark status as finish or failed
        if (is_file($storage_path.'/concat.mp4') === true && file_exists($storage_path.'/concat.mp4') === true) {
            $this->setStatus(self::$JOB_STATUS_FINISH);
        }else{
            $this->setStatus(self::$JOB_STATUS_FAILED);
        }
    }

    public function setStatus($status) {
        $this->jobs->status = $status;
        $this->jobs->save();
    }

    public function getVideoFrameName($image, $suffix) {
        return $image->getBasename(".".$image->getExtension()).'-'.$suffix.'.mp4';
    }

    public function addToConcatFile($storage_path, $filename) {
        file_put_contents($storage_path.'/concat.txt', sprintf("file %s/%s\n", $storage_path, $filename), FILE_APPEND);
    }

}
