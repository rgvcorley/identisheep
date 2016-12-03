<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Help extends Model {

	public static $help_text =
        [
        'addewe'    =>  ['sheep/addewe','Help for Add a ewe page',
            'This is the help text <br>this is after the line break'],
        'seek'      =>  ['sheep/seek','Help for Sheep finder page',
            'Flock Number entered must be six digits - i.e. excluding the \'UK\' and the leading zero.<br>
            The Tag number can be entered without the leading zeros but will always be shown with 5 digits.'],
        'batchops'  =>  ['batch/batchops','.csv File entry',
            '<p>First prepare your .csv File. This can be from a tag reader, or a file can be generated from 
             a market list of tag reads. <br> To do this, scan the document with \'OCR\' and save it as a text 
             (.txt) file. Then open the file in e.g. notepad. <br> To load correctly the file must have EXACTLY 
             TWO lines above the tag list, so adjust this accordingly. <br> The third line must contain all 
             the tag data, and each tag number <br> is separated from the following one with a comma (.csv = 
             comma separated variables) like this:- <br> UK012312300051,UK012312300053,UK012312300088,UK012312300151
             <br> Spaces will cause a mis-read, and a there is no final comma on the line, it will cause a \'sheep\' 
             with all zeros to be loaded. </p>
             The default date is today, change this if you want to.<br>Enter the holding number or name 
             of the destination for the sheep.<br>Select your previously prepared and saved file, and use the 
             \'check\' button to generate a list <br>which you can use to see whether your file is being read 
             correctly.</p>
             If you are satisfied with the list, press the backspace key and then the \'load\' button.<br> Now the 
             list will be loaded into your \'off list\' (Sheep Off Holding) and also removed from the \'all sheep\' 
             list.<br>'],
        'batch'     =>  ['batch/batch','Batch of Sheep onto Holding','Help is obvious here<br> ']


    ];

}
