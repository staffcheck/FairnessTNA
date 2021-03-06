<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id: textarea.php,v 1.11 2004/02/28 22:10:16 avb Exp $

require_once("HTML/QuickForm/element.php");

/**
 * HTML class for a textarea type field
 *
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.0
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_textarea extends HTML_QuickForm_element
{
    // {{{ properties

    /**
     * Field value
     * @var       string
     * @since     1.0
     * @access    private
     */
    public static $_value = null;

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     *
     * @param     string    Input field name attribute
     * @param     mixed     Label(s) for a field
     * @param     mixed     Either a typical HTML attribute string or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    public function HTML_QuickForm_textarea($elementName = null, $elementLabel = null, $attributes = null)
    {
        //HTML_QuickForm_element::HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        new HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        self::$_persistantFreeze = true;
        self::$_type = 'textarea';
    } //end constructor

    // }}}
    // {{{ setName()

    /**
     * Sets the input field name
     *
     * @param     string $name Input field name attribute
     * @since     1.0
     * @access    public
     * @return    void
     */
    public static function setName($name)
    {
        self::updateAttributes(array('name' => $name));
    } //end func setName

    // }}}
    // {{{ getName()

    /**
     * Returns the element name
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    public static function getName()
    {
        return self::getAttribute('name');
    } //end func getName

    // }}}
    // {{{ setValue()

    /**
     * Sets wrap type for textarea element
     *
     * @param     string $wrap Wrap type
     * @since     1.0
     * @access    public
     * @return    void
     */
    public static function setWrap($wrap)
    {
        self::updateAttributes(array('wrap' => $wrap));
    } //end func setValue

    // }}}
    // {{{ getValue()

    /**
     * Sets height in rows for textarea element
     *
     * @param     string $rows Height expressed in rows
     * @since     1.0
     * @access    public
     * @return    void
     */
    public static function setRows($rows)
    {
        self::updateAttributes(array('rows' => $rows));
    } // end func getValue

    // }}}
    // {{{ setWrap()

    /**
     * Sets width in cols for textarea element
     *
     * @param     string $cols Width expressed in cols
     * @since     1.0
     * @access    public
     * @return    void
     */
    public static function setCols($cols)
    {
        self::updateAttributes(array('cols' => $cols));
    } //end func setWrap

    // }}}
    // {{{ setRows()

    /**
     * Returns the textarea element in HTML
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    public static function toHtml()
    {
        if (self::$_flagFrozen) {
            return self::getFrozenHtml();
        } else {
            return self::_getTabs() .
                '<textarea' . self::_getAttrString(self::$_attributes) . '>' .
                // because we wrap the form later we don't want the text indented
                preg_replace("/(\r\n|\n|\r)/", '&#010;', htmlspecialchars(self::$_value)) .
                '</textarea>';
        }
    } //end func setRows

    // }}}
    // {{{ setCols()

    /**
     * Returns the value of field without HTML tags (in this case, value is changed to a mask)
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    public static function getFrozenHtml()
    {
        $value = htmlspecialchars(self::getValue());
        if (self::getAttribute('wrap') == 'off') {
            $html = self::_getTabs() . '<pre>' . $value . "</pre>\n";
        } else {
            $html = nl2br($value) . "\n";
        }
        return $html . self::_getPersistantData();
    } //end func setCols

    // }}}
    // {{{ toHtml()

    /**
     * Returns the value of the form element
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    public static function getValue()
    {
        return self::$_value;
    } //end func toHtml

    // }}}
    // {{{ getFrozenHtml()

    /**
     * Sets value for textarea element
     *
     * @param     string $value Value for textarea element
     * @since     1.0
     * @access    public
     * @return    void
     */
    public static function setValue($value)
    {
        self::$_value = $value;
    } //end func getFrozenHtml

    // }}}
} //end class HTML_QuickForm_textarea;
