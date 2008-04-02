<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2008 The PHP Reader Project Workgroup. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the project workgroup nor the names of its
 *    contributors may be used to endorse or promote products derived from this
 *    software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    php-reader
 * @subpackage ID3
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: EQU2.php 65 2008-04-02 15:22:46Z svollbehr $
 * @since      ID3v2.4.0
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * The <i>Equalisation (2)</i> is another subjective, alignment frame. It allows
 * the user to predefine an equalisation curve within the audio file. There may
 * be more than one EQU2 frame in each tag, but only one with the same
 * identification string.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 65 $
 * @since      ID3v2.4.0
 */
final class ID3_Frame_EQU2 extends ID3_Frame
{
  /**
   * Interpolation type that defines that no interpolation is made. A jump from
   * one adjustment level to another occurs in the middle between two adjustment
   * points.
   */
  const BAND = 0;
  
  /**
   * Interpolation type that defines that interpolation between adjustment
   * points is linear.
   */
  const LINEAR = 1;

  /** @var integer */
  private $_interpolation;
  
  /** @var string */
  private $_device;
  
  /** @var Array */
  private $_adjustments;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader = null)
  {
    parent::__construct($reader);
    
    if ($reader === null)
      return;
    
    $this->_interpolation = Transform::fromInt8($this->_data[0]);
    list ($this->_device, $this->_data) =
      preg_split("/\\x00/", substr($this->_data, 1), 2);
    
    for ($i = 0; $i < strlen($this->_data); $i += 8)
      $this->_adjustments
        [Transform::fromInt16BE(substr($this->_data, $j, 2)) / 2] = 
          Transform::fromInt16BE(substr($this->_data, $j + 2, 2)) / 512;
    sort($this->_adjustments);
  }
  
  /**
   * Returns the interpolation method. The interpolation method describes which
   * method is preferred when an interpolation between the adjustment point that
   * follows.
   *
   * @return integer
   */
  public function getInterpolation() { return $this->_interpolation; }
  
  /**
   * Sets the interpolation method. The interpolation method describes which
   * method is preferred when an interpolation between the adjustment point that
   * follows.
   *
   * @param integer $interpolation The interpolation method code.
   */
  public function setInterpolation($interpolation)
  {
    $this->_interpolation = $interpolation;
  }
  
  /**
   * Returns the device where the adjustments should apply.
   *
   * @return string
   */
  public function getDevice() { return $this->_device; }
   
  /**
   * Sets the device where the adjustments should apply.
   *
   * @param string $device The device.
   */
  public function setDevice($device) { $this->_device = $device; }
   
  /**
   * Returns the array containing adjustments having frequencies as keys and
   * their corresponding adjustments as values.
   *
   * Adjustment points are ordered by frequency.
   * 
   * @return Array
   */
  public function getAdjustments() { return $this->_adjustments; }
  
  /**
   * Adds a volume adjustment setting for given frequency. The frequency can
   * have a value from 0 to 32767 Hz, and the adjustment +/- 64 dB with a
   * precision of 0.001953125 dB.
   * 
   * @return Array
   */
  public function addAdjustment($frequency, $adjustment)
  {
    $this->_adjustments[$frequency * 2] = $adjustment * 512;
    sort($this->_adjustments);
  }
   
  /**
   * Sets the adjustments array. The array must have frequencies as keys and
   * their corresponding adjustments as values. The frequency can have a value
   * from 0 to 32767 Hz, and the adjustment +/- 64 dB with a precision of
   * 0.001953125 dB. One frequency should only be described once in the frame.
   * 
   * @param Array $adjustments The adjustments array.
   */
  public function setAdjustments($adjustments)
  {
    foreach ($adjustments as $frequency => $adjustment)
      $this->_adjustments[$frequency * 2] = $adjustment * 512;
    sort($this->_adjustments);
  }
}
