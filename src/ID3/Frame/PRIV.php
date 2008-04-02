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
 * @version    $Id: PRIV.php 65 2008-04-02 15:22:46Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * The <i>Private frame</i> is used to contain information from a software
 * producer that its program uses and does not fit into the other frames. The
 * frame consists of an owner identifier string and the binary data. The owner
 * identifier is URL containing an email address, or a link to a location where
 * an email address can be found, that belongs to the organisation responsible
 * for the frame. Questions regarding the frame should be sent to the indicated
 * email address. The tag may contain more than one PRIV frame but only with
 * different contents.
 * 
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 65 $
 */
final class ID3_Frame_PRIV extends ID3_Frame
{
  /** @var string */
  private $_id;
  
  /** @var string */
  private $_privateData;
  
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
    
    list($this->_id, $this->_privateData) =
      preg_split("/\\x00/", $this->_data, 2);
  }
  
  /**
   * Returns the owner identifier string.
   * 
   * @return string
   */
  public function getIdentifier() { return $this->_id; }
  
  /**
   * Sets the owner identifier string.
   * 
   * @param string $id The owner identifier string.
   */
  public function setIdentifier($id) { $this->_id = $id; }
  
  /**
   * Returns the private binary data associated with the frame.
   * 
   * @return string
   */
  public function getData() { return $this->_privateData; }
  
  /**
   * Sets the private binary data associated with the frame.
   * 
   * @param string $privateData The private binary data string.
   */
  public function setData($privateData) { $this->_privateData = $privateData; }
  
  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    parent::setData($this->_id . "\0" . $this->_privateData);
    return parent::__toString();
  }
}
