<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2008-2009 The PHP Reader Project Workgroup. All rights
 * reserved.
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
 * @subpackage ASF
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: BitrateMutualExclusion.php 139 2009-02-19 14:10:37Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Bitrate Mutual Exclusion Object</i> identifies video streams that have
 * a mutual exclusion relationship to each other (in other words, only one of
 * the streams within such a relationship can be streamed at any given time and
 * the rest are ignored). One instance of this object must be present for each
 * set of objects that contains a mutual exclusion relationship. All video
 * streams in this relationship must have the same frame size. The exclusion
 * type is used so that implementations can allow user selection of common
 * choices, such as bit rate.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 139 $
 */
final class ASF_Object_BitrateMutualExclusion extends ASF_Object
{
  const MUTEX_LANGUAGE = "d6e22a00-35da-11d1-9034-00a0c90349be";
  const MUTEX_BITRATE = "d6e22a01-35da-11d1-9034-00a0c90349be";
  const MUTEX_UNKNOWN = "d6e22a02-35da-11d1-9034-00a0c90349be";
  
  /** @var string */
  private $_exclusionType;
  
  /** @var Array */
  private $_streamNumbers = array();
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ASF file.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($reader === null)
      return;
    
    $this->_exclusionType = $this->_reader->readGUID();
    $streamNumbersCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $streamNumbersCount; $i++)
      $this->_streamNumbers[] = $this->_reader->readUInt16LE();
  }
  
  /**
   * Returns the nature of the mutual exclusion relationship.
   *
   * @return string
   */
  public function getExclusionType() { return $this->_exclusionType; }
  
  /**
   * Sets the nature of the mutual exclusion relationship.
   * 
   * @param string $exclusionType The nature of the mutual exclusion
   *        relationship.
   */
  public function setExclusionType($exclusionType)
  {
    $this->_exclusionType = $exclusionType;
  }
  
  /**
   * Returns an array of stream numbers.
   *
   * @return Array
   */
  public function getStreamNumbers() { return $this->_streamNumbers; }
  
  /**
   * Sets the array of stream numbers.
   * 
   * @param Array $streamNumbers The array of stream numbers.
   */
  public function setStreamNumbers($streamNumbers)
  {
    $this->_streamNumbers = $streamNumbers;
  }
  
  /**
   * Returns the whether the object is required to be present, or whether
   * minimum cardinality is 1.
   * 
   * @return boolean
   */
  public function isMandatory() { return false; }
  
  /**
   * Returns whether multiple instances of this object can be present, or
   * whether maximum cardinality is greater than 1.
   * 
   * @return boolean
   */
  public function isMultiple() { return false; }
  
  /**
   * Returns the object data with headers.
   *
   * @return string
   */
  public function __toString()
  {
    $data = 
      Transform::toGUID($this->_exclusionType) .
      Transform::toUInt16LE($streamNumbersCount = count($this->_streamNumbers));
    for ($i = 0; $i < $streamNumbersCount; $i++)
      $data .= Transform::toUInt16LE($this->_streamNumbers[$i]);
    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
