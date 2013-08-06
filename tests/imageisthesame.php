<?php
/**
 * Checks if two images contain the same image, pixel by pixel
 *
 * @param mixed $file1 Filename for picture 1 OR gd image resource
 * @param mixed $file2 Filename for picture 2 OR gd iamge resource
 *
 * @return boolean true if they are the same, false if not
 * @throws Exception
 */
function imageisthesame($file1, $file2)
{
    //echo $file1 . ' - ' . $file2 . "\n";
    if (is_string($file1)) {
        if (!file_exists($file1)) {
            throw new Exception('File 1 does not exist' . $file1);
        }

        $i1 = imagecreatefromstring(file_get_contents($file1));
        if ($i1 === false) {
            throw new Exception('Image 1 could no be opened' . $file1);
        }
    } else {
        $i1 = $file1;
    }

    if (is_string($file2)) {
        if (!file_exists($file2)) {
            throw new Exception('File 2 does not exist' . $file2);
        }

        $i2 = imagecreatefromstring(file_get_contents($file2));
        if ($i2 === false) {
            throw new Exception('Image 2 could no be opened' . $file2);
        }
    } else {
        $i2 = $file2;
    }

    $sx1 = imagesx($i1);
    $sy1 = imagesy($i1);
    if ($sx1 != imagesx($i2) || $sy1 != imagesy($i2)) {
        //image size does not match
        return false;
    }

    for ($x = 0; $x < $sx1; $x++) {
        for ($y = 0; $y < $sy1; $y++) {
            $rgb1 = imagecolorat($i1, $x, $y);
            $pix1 = imagecolorsforindex($i1, $rgb1);

            $rgb2 = imagecolorat($i2, $x, $y);
            $pix2 = imagecolorsforindex($i2, $rgb2);

            //echo implode(',',$pix1) . ' - ' . implode(',',$pix2) . "\n";
            if ($pix1 != $pix2) {
                return false;
            }

        }
    }

    return true;
}
