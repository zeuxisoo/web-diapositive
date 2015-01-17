<?php
use Diapositive\Models;

class MakeVideoJob {

    public function perform() {
        $user_id        = $this->args['user_id'];
        $slideshow_uuid = $this->args['slideshow_uuid'];

        $jobs = Models\Job::where('user_id', $user_id)
                ->where('slideshow_uuid', $slideshow_uuid)
                ->findOne();

        // TODO - Convert the images to video

        $jobs->status = 'finish';
        $jobs->save();
    }

}
