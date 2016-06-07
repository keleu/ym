<?php
require('class/BCGFont.php');
require('class/BCGColor.php');
require('class/BCGDrawing.php'); 
require('class/BCGcode128.barcode.php'); 
class topshop_barcode_Barcode{
    function makeBarcode($data = null){
        $codebar = 'BCGcode128'; //修改1

        // Including the barcode technology
        // include('class/'.$codebar.'.barcode.php'); 

        // Loading Font
        $font = new BCGFont('./class/font/Arial.ttf', 10);

        // The arguments are R, G, B for color.
        $color_black = new BCGColor(0, 0, 0);
        $color_white = new BCGColor(255, 255, 255); 

        $code = new $codebar();
        $code->setScale(1); // Resolution
        $code->setThickness(25); // Thickness
        $code->setForegroundColor($color_black); // Color of bars
        $code->setBackgroundColor($color_white); // Color of spaces
        $code->setFont($font); // Font (or 0)
        $text = $data; //修改2：内容
        $code->parse($text); 
        /* Here is the list of the arguments
        1 - Filename (empty : display on screen)
        2 - Background color */
        $drawing = new BCGDrawing(ROOT_DIR.'\resource\barcode\temp.png', $color_white);
        $drawing->setBarcode($code);
        $drawing->draw();
        // Header that says it is an image (remove it if you save the barcode to a file)
        // header('Content-Type: image/png');

        // Draw (or save) the image into PNG format.
        return $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
    }
}