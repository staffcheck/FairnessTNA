<?php
/**
 * To be included by Driver __construct() method
 *
 * @author Philippe.Jausions @at@ 11abacus.com
 **/
$this->_supported_image_types = array(
    'art' => 'r',
    'avi' => 'r',
    'avs' => 'rw',
    'bmp' => 'rw',
//  'cgm'   => 'r',
    'cin' => 'rw',
    'cmyk' => 'rw',
    'cur' => 'r',
    'cut' => 'r',
    'dcm' => 'r',
    'dcx' => 'rw',
    'dib' => 'rw',
    'dpx' => 'rw',
    'epdf' => 'rw',
//  'epi'   => 'rw',    // Requires Ghostscript
//  'eps'   => 'rw',    // Requires Ghostscript
//  'eps2'  => 'w',     // Requires Ghostscript
//  'eps3'  => 'w',     // Requires Ghostscript
//  'epsf'  => 'rw',    // Requires Ghostscript
//  'epsi'  => 'rw',    // Requires Ghostscript
//  'ept'   => 'rw',    // Requires Ghostscript
//  'fig'   => 'r',
    'fits' => 'rw',
//  'fpx'   => 'rw',
    'gif' => 'rw',
//  'gplt'  => 'r',
    'gray' => 'rw',
//  'hpgl'  => 'r',
//  'html'  => 'rw',
    'ico' => 'r',
//  'jbig'  => 'rw',
    'jng' => 'rw',
//  'jp2'   => 'rw',
//  'jpc'   => 'rw',
//  'jpeg'  => 'rw',
//  'man'   => 'r',
    'mat' => 'r',
    'miff' => 'rw',
    'mono' => 'rw',
    'mng' => 'rw',
//  'mpeg'  => 'rw',
//  'm2v'   => 'rw',
    'mpc' => 'rw',
    'msl' => 'rw',
    'mtv' => 'rw',
    'mvg' => 'rw',
    'otb' => 'rw',
    'p7' => 'rw',
    'palm' => 'rw',
    'pbm' => 'rw',
    'pcd' => 'rw',
    'pcds' => 'rw',
    'pcl' => 'w',
    'pcx' => 'rw',
    'pdb' => 'rw',
    'pdf' => 'w',      // Requires Ghostscript to read
    'pfa' => 'r',
    'pfb' => 'r',
    'pgm' => 'rw',
    'picon' => 'rw',
    'pict' => 'rw',
    'pix' => 'r',
    'png' => 'rw',
    'pnm' => 'rw',
    'ppm' => 'rw',
//  'ps'    => 'rw',     // Requires Ghostscript
//  'ps2'   => 'rw',     // Requires Ghostscript
//  'ps3'   => 'rw',     // Requires Ghostscript
    'psd' => 'rw',
    'ptif' => 'rw',
    'pwp' => 'r',
//  'rad'   => 'r',
    'rgb' => 'rw',
    'rgba' => 'rw',
    'rla' => 'r',
    'rle' => 'r',
    'sct' => 'r',
    'sfw' => 'r',
    'sgi' => 'rw',
//  'shtml' => 'w',
    'sun' => 'rw',
//  'svg'   => 'rw',
    'tga' => 'rw',
//  'tiff'  => 'rw',
    'tim' => 'r',
//  'ttf'   => 'r',
    'txt' => 'rw',
    'uil' => 'w',
    'uyvy' => 'rw',
    'vicar' => 'rw',
    'viff' => 'rw',
    'wbmp' => 'rw',
    'wpg' => 'r',
    'xbm' => 'rw',
    'xcf' => 'r',
    'xpm' => 'rw',
    'xwd' => 'rw',
    'yuv' => 'rw');

if (OS_WINDOWS) {
    $this->_supported_image_types['emf'] = 'r';
}
