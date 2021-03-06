<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: fpdf_etiquette.class.php,v 1.9 2015-03-19 13:35:18 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

if (! defined('FPDF_ETIQUETTE_CLASS')) {
define('FPDF_ETIQUETTE_CLASS', 1);

define('FPDF_FONTPATH',$class_path.'/font/');
define('FPDF_CB_TEMPPATH', $base_path.'/temp/');

include("$class_path/barcode/barcode.php");
include("$class_path/barcode/c128aobject.php");
include("$class_path/barcode/c128bobject.php");
include("$class_path/barcode/c128cobject.php");
include("$class_path/barcode/c39object.php");
include("$class_path/barcode/i25object.php");

class FPDF_Etiquette extends FPDF
{
// private properties

// infos planche d'�tiquettes
var $topMargin;         // Marge du haut de la planche d'�tiquettes
var $bottomMargin;      // Marge du bas de la planche d'�tiquettes
var $leftMargin;        // Marge de gauche de la planche d'�tiquettes
var $rightMargin;       // Marge de droite de la planche d'�tiquettes

var $xSticksPadding;    // Espacement horizontal entre 2 �tiquettes
var $ySticksPadding;    // Espacement vertical entre 2 �tiquettes

var $nbrXSticks;        // Nombre d'�tiquettes en largeur
var $nbrYSticks;        // Nombre d'�tiquettes en hauteur

var $stickTopMargin;    // Marge int�rieure haut de l'�tiquette
var $stickBottomMargin; // Marge int�rieure bas de l'�tiquette
var $stickLeftMargin;   // Marge int�rieure gauche de l'�tiquette
var $stickRightMargin;  // Marge int�rieure droite de l'�tiquette

var $xStick;            // Position courante de l'�tiquette (unit� : �tiquette)
var $yStick;            // Position courante de l'�tiquette (unit� : �tiquette)
var $nbrSticks;         // Nombre de sticks ajout� avec AddStick

// infos code barre
var $cbXRes;            // R�solution du code barres
var $cbFontSize;        // Taille de la police du code barre
var $cbStyle;           // Style du code barre
var $angle = 0;

/****************************************************************************
*                                                                           *
*                              Public methods                               *
*                                                                           *
****************************************************************************/
function FPDF_Etiquette($nbrXSticks, $nbrYSticks, $orientation='P',$unit='mm',$format='A4')
{
	$this->FPDF($orientation, $unit, $format);

	// Initialisation des propri�t�s
	$this->nbrXSticks = $nbrXSticks;
	$this->nbrYSticks = $nbrYSticks;
	$this->nbrSticks = 0;

	// par d�faut, prend toute la feuille
	// Marges de la planche
	$this->SetPageMargins(0, 0, 0, 0);
	// Ecart entre les �tiquettes
	$this->SetSticksPadding(0, 0);
	// Marge int�rieure des �tiquettes
	$this->SetSticksMargins(5, 5, 5, 5);

	// infos code barres
	$this->SetCBFontSize(3);
	$this->SetCBXRes(1);

	// autres
	$this->SetAutoPageBreak(false);
}

function SetPageMargins($top, $bottom, $left, $right)
{
	$this->topMargin=$top;
	$this->bottomMargin=$bottom;
	$this->leftMargin=$left;
	$this->rightMargin=$right;
}

function SetSticksPadding($xPadding, $yPadding)
{
	$this->xSticksPadding = $xPadding;
	$this->ySticksPadding = $yPadding;
}

function SetSticksMargins($top,$bottom, $left, $right)
{
	$this->stickTopMargin=$top;
	$this->stickBottomMargin=$bottom;
	$this->stickLeftMargin=$left;
	$this->stickRightMargin=$right;
}

function SetCBFontSize($size)
{
	if ($size > 5)
		$size = 5;
	elseif ($size < 1)
		$size = 1;
	$this->cbFontSize = $size;
}

function SetCBXRes($xres)
{
	if ($xres < 1)
		$xres = 1;
	elseif ($xres > 3)
		$xres = 3;
	$this->cbXRes = $xres;
}

function SetCBStyle($style)
{
	$this->cbStyle = $style;
}

function GetStickWidth()
{
	return ($this->w - ($this->leftMargin + $this->rightMargin) - (($this->nbrXSticks-1) * $this->xSticksPadding) )  /  $this->nbrXSticks;
}

function GetStickHeight()
{
	return ($this->h - ($this->topMargin + $this->bottomMargin) - (($this->nbrYSticks-1) * $this->ySticksPadding) )  /  $this->nbrYSticks;
}

function AddStick()
{
	if ($this->nbrSticks == 0)
	{
		$this->xStick = 0;
		$this->yStick = 0;
		$this->AddPage();
	}
	else
	{
		$this->xStick++;
		if ($this->xStick >= $this->nbrXSticks)
		{
			$this->yStick++;
			$this->xStick = 0;
			if ($this->yStick >= $this->nbrYSticks)
			{
				$this->AddPage();
				$this->yStick = 0;
			}
		}
	}

	$this->nbrSticks++;
}

function GetStickX()
{	//Appels � refaire en utilisant seulement l'espacement entre 2 etiquettes (sinon Pb arrondi sur calcul position)
	if ($this->xSticksPadding) {
		return $this->leftMargin + ($this->xStick*$this->xSticksPadding);
	} else {
		return $this->leftMargin + (($this->w - ($this->leftMargin + $this->rightMargin)) / $this->nbrXSticks) * $this->xStick;
	}
	
}

function GetStickY()
{	//Appels � refaire en utilisant seulement l'espacement entre 2 etiquettes (sinon Pb arrondi sur calcul position)
	if ($this->xSticksPadding) {
		return $this->topMargin + ((($this->yStick))*$this->ySticksPadding);
	} else {
		return $this->topMargin + (($this->h - ($this->topMargin + $this->bottomMargin)) / $this->nbrYSticks) * $this->yStick;
	}
	
}

function GetNbrSticks()
{
	return $this->nbrSticks;
}

function DrawBarcode($cb, $x,$y, $w,$h, $type='')
{
	$type = strToLower($type);
	$len = strlen($cb);

	// calcule la largeur du code barre en pixels
	switch ($type)
	{
		case 'c128a' :
		case 'c128b' :
		case 'c128c' :
			$width = (35 + $len*11)*$this->cbXRes;
			break;
		case 'i25' :
			$width = (8 + $len*7)*$this->cbXRes;
			break;
		case 'c39' :
		default :
			$width = (($len+2)*12 + $len+1)*$this->cbXRes;
			break;
	}
	// calcule la hauteur en pixels � partir de la largeur
	$height = ($width * $h) / $w;

	// cr�e le code barre
	switch ($type)
	{
		case 'c128a' :
			$cbi = new C128AObject($width, $height, $this->cbStyle, "$cb");
			break;
		case 'c128b' :
			$cbi = new C128BObject($width, $height, $this->cbStyle, "$cb");
			break;
		case 'c128c' :
			$cbi = new C128CObject($width, $height, $this->cbStyle, "$cb");
			break;
		case 'i25' :
			$cbi = new I25Object($width, $height, $this->cbStyle, "$cb");
			break;
		case 'c39' :
		default :
			$cbi = new C39Object($width, $height, $this->cbStyle, "$cb");
			break;
	}

	// dessine et incorpore au pdf.
	$cbi->SetFont($this->cbFontSize);
	$cbi->DrawObject($this->cbXRes);
	$filename = FPDF_CB_TEMPPATH."cb".time().$cb;
	$cbi->SaveTo($filename);
	$cbi->DestroyObject();
	$this->Image($filename, $x, $y, $w, $h, "png");
	unlink($filename);
}


} // fin de la classe FPDF_Etiquette


class UFPDF_Etiquette extends UFPDF
{
// private properties

// infos planche d'�tiquettes
var $topMargin;         // Marge du haut de la planche d'�tiquettes
var $bottomMargin;      // Marge du bas de la planche d'�tiquettes
var $leftMargin;        // Marge de gauche de la planche d'�tiquettes
var $rightMargin;       // Marge de droite de la planche d'�tiquettes

var $xSticksPadding;    // Espacement horizontal entre 2 �tiquettes
var $ySticksPadding;    // Espacement vertical entre 2 �tiquettes

var $nbrXSticks;        // Nombre d'�tiquettes en largeur
var $nbrYSticks;        // Nombre d'�tiquettes en hauteur

var $stickTopMargin;    // Marge int�rieure haut de l'�tiquette
var $stickBottomMargin; // Marge int�rieure bas de l'�tiquette
var $stickLeftMargin;   // Marge int�rieure gauche de l'�tiquette
var $stickRightMargin;  // Marge int�rieure droite de l'�tiquette

var $xStick;            // Position courante de l'�tiquette (unit� : �tiquette)
var $yStick;            // Position courante de l'�tiquette (unit� : �tiquette)
var $nbrSticks;         // Nombre de sticks ajout� avec AddStick

// infos code barre
var $cbXRes;            // R�solution du code barres
var $cbFontSize;        // Taille de la police du code barre
var $cbStyle;           // Style du code barre

/****************************************************************************
*                                                                           *
*                              Public methods                               *
*                                                                           *
****************************************************************************/
function UFPDF_Etiquette($nbrXSticks, $nbrYSticks, $orientation='P',$unit='mm',$format='A4')
{
	$this->UFPDF($orientation, $unit, $format);

	// Initialisation des propri�t�s
	$this->nbrXSticks = $nbrXSticks;
	$this->nbrYSticks = $nbrYSticks;
	$this->nbrSticks = 0;

	// par d�faut, prend toute la feuille
	// Marges de la planche
	$this->SetPageMargins(0, 0, 0, 0);
	// Ecart entre les �tiquettes
	$this->SetSticksPadding(0, 0);
	// Marge int�rieure des �tiquettes
	$this->SetSticksMargins(5, 5, 5, 5);

	// infos code barres
	$this->SetCBFontSize(3);
	$this->SetCBXRes(1);

	// autres
	$this->SetAutoPageBreak(false);
}

function SetPageMargins($top, $bottom, $left, $right)
{
	$this->topMargin=$top;
	$this->bottomMargin=$bottom;
	$this->leftMargin=$left;
	$this->rightMargin=$right;
}

function SetSticksPadding($xPadding, $yPadding)
{
	$this->xSticksPadding = $xPadding;
	$this->ySticksPadding = $yPadding;
}

function SetSticksMargins($top,$bottom, $left, $right)
{
	$this->stickTopMargin=$top;
	$this->stickBottomMargin=$bottom;
	$this->stickLeftMargin=$left;
	$this->stickRightMargin=$right;
}

function SetCBFontSize($size)
{
	if ($size > 5)
		$size = 5;
	elseif ($size < 1)
		$size = 1;
	$this->cbFontSize = $size;
}

function SetCBXRes($xres)
{
	if ($xres < 1)
		$xres = 1;
	elseif ($xres > 3)
		$xres = 3;
	$this->cbXRes = $xres;
}

function SetCBStyle($style)
{
	$this->cbStyle = $style;
}

function GetStickWidth()
{
	return ($this->w - ($this->leftMargin + $this->rightMargin) - (($this->nbrXSticks-1) * $this->xSticksPadding) )  /  $this->nbrXSticks;
}

function GetStickHeight()
{
	return ($this->h - ($this->topMargin + $this->bottomMargin) - (($this->nbrYSticks-1) * $this->ySticksPadding) )  /  $this->nbrYSticks;
}

function AddStick()
{
	if ($this->nbrSticks == 0)
	{
		$this->xStick = 0;
		$this->yStick = 0;
		$this->AddPage();
	}
	else
	{
		$this->xStick++;
		if ($this->xStick >= $this->nbrXSticks)
		{
			$this->yStick++;
			$this->xStick = 0;
			if ($this->yStick >= $this->nbrYSticks)
			{
				$this->AddPage();
				$this->yStick = 0;
			}
		}
	}

	$this->nbrSticks++;
}

function GetStickX()
{	//Appels � refaire en utilisant seulement l'espacement entre 2 etiquettes (sinon Pb arrondi sur calcul position)
	if ($this->xSticksPadding) {
		return $this->leftMargin + ($this->xStick*$this->xSticksPadding);
	} else {
		return $this->leftMargin + (($this->w - ($this->leftMargin + $this->rightMargin)) / $this->nbrXSticks) * $this->xStick;
	}
	
}

function GetStickY()
{	//Appels � refaire en utilisant seulement l'espacement entre 2 etiquettes (sinon Pb arrondi sur calcul position)
	if ($this->xSticksPadding) {
		return $this->topMargin + ((($this->yStick))*$this->ySticksPadding);
	} else {
		return $this->topMargin + (($this->h - ($this->topMargin + $this->bottomMargin)) / $this->nbrYSticks) * $this->yStick;
	}
	
}

function GetNbrSticks()
{
	return $this->nbrSticks;
}

function DrawBarcode($cb, $x,$y, $w,$h, $type='')
{
	$type = strToLower($type);
	$len = strlen($cb);

	// calcule la largeur du code barre en pixels
	switch ($type)
	{
		case 'c128a' :
		case 'c128b' :
		case 'c128c' :
			$width = (35 + $len*11)*$this->cbXRes;
			break;
		case 'i25' :
			$width = (8 + $len*7)*$this->cbXRes;
			break;
		case 'c39' :
		default :
			$width = (($len+2)*12 + $len+1)*$this->cbXRes;
			break;
	}
	// calcule la hauteur en pixels � partir de la largeur
	$height = ($width * $h) / $w;

	// cr�e le code barre
	switch ($type)
	{
		case 'c128a' :
			$cbi = new C128AObject($width, $height, $this->cbStyle, "$cb");
			break;
		case 'c128b' :
			$cbi = new C128BObject($width, $height, $this->cbStyle, "$cb");
			break;
		case 'c128c' :
			$cbi = new C128CObject($width, $height, $this->cbStyle, "$cb");
			break;
		case 'i25' :
			$cbi = new I25Object($width, $height, $this->cbStyle, "$cb");
			break;
		case 'c39' :
		default :
			$cbi = new C39Object($width, $height, $this->cbStyle, "$cb");
			break;
	}

	// dessine et incorpore au pdf.
	$cbi->SetFont($this->cbFontSize);
	$cbi->DrawObject($this->cbXRes);
	$filename = FPDF_CB_TEMPPATH."cb".time().$cb;
	$cbi->SaveTo($filename);
	$cbi->DestroyObject();
	$this->Image($filename, $x, $y, $w, $h, "png");
	unlink($filename);
}


} // fin de la classe FPDF_Etiquette

} // fin de d�finition de FPDF_ETIQUETTE_CLASS
