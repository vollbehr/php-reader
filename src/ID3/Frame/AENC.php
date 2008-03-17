<?php
/**
 * PHP Reader Library
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the BEHR Software Systems nor the names of its
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
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Id: AENC.php 11 2008-03-12 12:06:41Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * The <i>Audio encryption</i> indicates if the actual audio stream is
 * encrypted, and by whom.
 *
 * The identifier is a URL containing an email address, or a link to a location
 * where an email address can be found, that belongs to the organisation
 * responsible for this specific encrypted audio file. Questions regarding the
 * encrypted audio should be sent to the email address specified. There may be
 * more than one AENC frame in a tag, but only one with the same owner
 * identifier.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev: 11 $
 */
final class ID3_Frame_AENC extends ID3_Frame
{
  /** @var string */
  private $_id;
  
  /** @var integer */
  private $_previewStart;
  
  /** @var integer */
  private $_previewLength;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    list($this->_id, $this->_data) = preg_split("/\\x00/", $this->_data, 2);
    $this->_previewStart = substr($this->_data, 0, 2);
    $this->_previewLength = substr($this->_data, 2, 2);
    $this->_data = substr($this->_data, 4);
  }

  /**
   * Returns the owner identifier string.
   * 
   * @return string
   */
  public function getIdentifier() { return $this->_id; }

  /**
   * Returns the pointer to an unencrypted part of the audio in frames.
   * 
   * @return integer
   */
  public function getPreviewStart() { return $this->_previewStart; }

  /**
   * Returns the length of the preview in frames.
   * 
   * @return integer
   */
  public function getPreviewLength() { return $this->_previewLength; }

  /**
   * Returns the encryption info.
   * 
   * @return string
   */
  public function getData() { return $this->_data; }
}
