<?php
require_once dirname(__DIR__).'/diapositive/application.php';

$app = new Diapositive\Application();
$app->registerConfig();
$app->registerAutoLoad();

$storage_path = STORAGE_ROOT.'/slideshows/54bf688e545a2';

// Get images
$images = new RegexIterator(
    new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($storage_path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
        RecursiveIteratorIterator::CATCH_GET_CHILD
    ),
    '/^.+\.(jpg)$/i'
);

$all_images = iterator_to_array($images, false);

// First image
$head_image = current($all_images);

// Last image
$tail_image = end($all_images);

// Slide settings
$fade_duration  = 0.7;
$fade_st        = 5-0.7;
$slide_duration = 5;
$slide_format   = "-pix_fmt yuvj420p -c:v libx264 -preset slow";

// Fade in (head image)
$command = new mikehaertl\shellcommand\Command(sprintf(
    'ffmpeg -loop 1 -i "%s" -vf fade=in:st=0:d=%s -t %s %s -y "%s"',
    $storage_path.'/'.$head_image->getFilename(),
    $fade_duration,
    $slide_duration,
    $slide_format,
    $storage_path.'/'.$head_image->getBasename(".".$head_image->getExtension()).'-head.mp4'
));
$command->execute();

// Single (all images)
foreach($all_images as $image) {
    $command = new mikehaertl\shellcommand\Command(sprintf(
        'ffmpeg -loop 1 -i "%s" -t %s %s -y "%s"',
        $storage_path.'/'.$image->getFilename(),
        $slide_duration,
        $slide_format,
        $storage_path.'/'.$image->getBasename(".".$image->getExtension()).'-middle.mp4'
    ));
    $command->execute();
}

// Transition (all images but without tail image)
for($i=0, $s=count($all_images) - 1; $i<$s; $i++) {
    $current_image = $all_images[$i];
    $next_image    = $all_images[$i+1];

    $command = new mikehaertl\shellcommand\Command(sprintf(
        'ffmpeg -loop 1 -i "%s" -loop 1 -i "%s" -filter_complex "[1:v][0:v]blend=all_expr=\'A*(if(gte(T,%s),1,T/%s))+B*(1-(if(gte(T,%s),1,T/%s)))\'" -t %s %s -y "%s"',
        $storage_path.'/'.$current_image->getFilename(),
        $storage_path.'/'.$next_image->getFilename(),
        $fade_duration,
        $fade_duration,
        $fade_duration,
        $fade_duration,
        $fade_duration,
        $slide_format,
        $storage_path.'/'.$current_image->getBasename(".".$current_image->getExtension()).'-transition.mp4'
    ));
    $command->execute();
}

// Fade out (tail image)
$command = new mikehaertl\shellcommand\Command(sprintf(
    'ffmpeg -loop 1 -i "%s" -vf fade=out:st=%s:d=%s -t %s %s -y "%s"',
    $storage_path.'/'.$tail_image->getFilename(),
    $fade_st,
    $fade_duration,
    $slide_duration,
    $slide_format,
    $storage_path.'/'.$tail_image->getBasename(".".$tail_image->getExtension()).'-tail.mp4'
));
$command->execute();

# Concat (all mp4)
for($i=0, $s=count($all_images); $i<$s; $i++) {
    $image    = $all_images[$i];
    $filename = $image->getBasename(".".$head_image->getExtension());

    $template = 'file %s/%s';

    // head
    if ($i === 0) {
        file_put_contents($storage_path.'/concat.txt', sprintf($template, $storage_path, $filename."-head.mp4\n"), FILE_APPEND);
        file_put_contents($storage_path.'/concat.txt', sprintf($template, $storage_path, $filename."-middle.mp4\n"), FILE_APPEND);
        file_put_contents($storage_path.'/concat.txt', sprintf($template, $storage_path, $filename."-transition.mp4\n"), FILE_APPEND);
    }

    // middle
    if ($i > 0 && $i < $s - 1) {
        file_put_contents($storage_path.'/concat.txt', sprintf($template, $storage_path, $filename."-middle.mp4\n"), FILE_APPEND);
        file_put_contents($storage_path.'/concat.txt', sprintf($template, $storage_path, $filename."-transition.mp4\n"), FILE_APPEND);
    }

    // tail
    if ($i === $s - 1) {
        file_put_contents($storage_path.'/concat.txt', sprintf($template, $storage_path, $filename."-middle.mp4\n"), FILE_APPEND);
        file_put_contents($storage_path.'/concat.txt', sprintf($template, $storage_path, $filename."-tail.mp4\n"), FILE_APPEND);
    }
}

$command = new mikehaertl\shellcommand\Command(sprintf(
    'ffmpeg -f concat -i "%s" -c copy -y "%s"',
    $storage_path.'/concat.txt',
    $storage_path.'/concat.mp4'
));
$command->execute();
