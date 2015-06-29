<?php
class randomImage
{
    /**
     * Usage:
     *
     * $imgSet = new randomImage($WikiTheme->file("images/pictures"));
     * $imgFile = "pictures/" . $imgSet->filename;
     */
    function randomImage($dirname)
    {

        $this->filename = ""; // Pick up your filename here.

        $_imageSet = new imageSet($dirname);
        $this->imageList = $_imageSet->getFiles();
        unset($_imageSet);

        if (empty($this->imageList)) {
            trigger_error(sprintf(_("%s is empty."), $dirname),
                E_USER_NOTICE);
        } else {
            $dummy = $this->pickRandom();
        }
    }

    function pickRandom()
    {
        $this->filename = $this->imageList[array_rand($this->imageList)];
        return $this->filename;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
