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
 * @subpackage ISO 14496
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: ID32.php 86 2008-04-25 07:30:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>ID3v2 Box</i> resides under the {@link ISO14496_Box_META Meta Box} and
 * stores ID3 version 2 meta-data. There may be more than one ID3v2 Box present
 * each with a different language code.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 86 $
 */
final class ISO14496_Box_ID32 extends ISO14496_Box_Full
{
  /** @var string */
  private $_language;
  
  /** @var ID3v2 */
  private $_tag;
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);
    
    $this->_language =
      chr(((($tmp = $this->_reader->readUInt16BE()) >> 10) & 0x1f) + 0x60) .
      chr((($tmp >> 5) & 0x1f) + 0x60) . chr(($tmp & 0x1f) + 0x60);
    $this->_tag = new ID3v2($this->_reader, array("readonly" => true));
  }
  
  /**
   * Returns the three byte language code to describe the language of this
   * media, according to {@link http://www.loc.gov/standards/iso639-2/
   * ISO 639-2/T}.
   * 
   * @return string
   */
  public function getLanguage() { return $this->_language; }
  
  /**
   * Returns the {@link ID3v2} tag class instance.
   *
   * @return string
   */
  public function getTag() { return $this->_tag; }
}
