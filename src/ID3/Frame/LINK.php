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
 * @version    $Id: LINK.php 11 2008-03-12 12:06:41Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * The <i>Linked information</i> frame is used to keep information duplication
 * as low as possible by linking information from another ID3v2 tag that might
 * reside in another audio file or alone in a binary file. It is recommended
 * that this method is only used when the files are stored on a CD-ROM or other
 * circumstances when the risk of file separation is low.
 *
 * Data should be retrieved from the first tag found in the file to which this
 * link points. There may be more than one LINK frame in a tag, but only one
 * with the same contents.
 *
 * A linked frame is to be considered as part of the tag and has the same
 * restrictions as if it was a physical part of the tag (i.e. only one
 * {@link ID3_Frame_RVRB} frame allowed, whether it's linked or not).
 *
 * Frames that may be linked and need no additional data are
 * {@link ID3_Frame_ASPI}, {@link ID3_Frame_ETCO}, {@link ID3_Frame_EQU2},
 * {@link ID3_Frame_MCDI}, {@link ID3_Frame_MLLT}, {@link ID3_Frame_OWNE},
 * {@link ID3_Frame_RVA2}, {@link ID3_Frame_RVRB}, {@link ID3_Frame_SYTC}, the
 * text information frames (ie frames descendats of
 * {@link ID3_Frame_AbstractText}) and the URL link frames (ie frames descendants
 * of {@link ID3_Frame_AbstractLink}).
 *
 * The {@link ID3_Frame_AENC}, {@link ID3_Frame_APIC}, {@link ID3_Frame_GEOB}
 * and {@link ID3_Frame_TXXX} frames may be linked with the content descriptor
 * as additional ID data.
 *
 * The {@link ID3_Frame_USER} frame may be linked with the language field as
 * additional ID data.
 *
 * The {@link ID3_Frame_PRIV} frame may be linked with the owner identifier as
 * additional ID data.
 *
 * The {@link ID3_Frame_COMM}, {@link ID3_Frame_SYLT} and {@link ID3_Frame_USLT}
 * frames may be linked with three bytes of language descriptor directly
 * followed by a content descriptor as additional ID data.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev: 11 $
 */
final class ID3_Frame_LINK extends ID3_Frame
{
  /** @var string */
  private $_target;

  /** @var string */
  private $_url;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_target = substr($this->_data, 0, 4);
    list($this->_url, $this->_data) =
      preg_split("/\\x00/", substr($this->_data, 4), 2);
  }
  
  /**
   * Returns the target tag identifier.
   * 
   * @return string
   */
  public function getTarget() { return $this->_target; }

  /**
   * Returns the target tag URL.
   * 
   * @return string
   */
  public function getURL() { return $this->_url; }
  
  /**
   * Returns the additional ID data.
   * 
   * @return string
   */
  public function getData() { return $this->_data; }
}
