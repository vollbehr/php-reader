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
 * @version    $Id: RBUF.php 11 2008-03-12 12:06:41Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * Sometimes the server from which an audio file is streamed is aware of
 * transmission or coding problems resulting in interruptions in the audio
 * stream. In these cases, the size of the buffer can be recommended by the
 * server using the <i>Recommended buffer size</i> frame. If the embedded info
 * flag is set then this indicates that an ID3 tag with the maximum size
 * described in buffer size may occur in the audio stream. In such case the tag
 * should reside between two MPEG frames, if the audio is MPEG encoded. If the
 * position of the next tag is known, offset to next tag may be used. The offset
 * is calculated from the end of tag in which this frame resides to the first
 * byte of the header in the next. This field may be omitted. Embedded tags are
 * generally not recommended since this could render unpredictable behaviour
 * from present software/hardware.
 *
 * For applications like streaming audio it might be an idea to embed tags into
 * the audio stream though. If the clients connects to individual connections
 * like HTTP and there is a possibility to begin every transmission with a tag,
 * then this tag should include a recommended buffer size frame. If the client
 * is connected to a arbitrary point in the stream, such as radio or multicast,
 * then the recommended buffer size frame should be included in every tag.
 *
 * The buffer size should be kept to a minimum. There may only be one "RBUF"
 * frame in each tag.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev: 11 $
 */
final class ID3_Frame_RBUF extends ID3_Frame
{
  /**
   * A flag to denote that an ID3 tag with the maximum size described in buffer
   * size may occur in the audio stream.
   */
  const EMBEDDED = 0x1;
  
  /** @var integer */
  private $_size;
  
  /** @var integer */
  private $_flags;
  
  /** @var integer */
  private $_offset;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_size = Transform::getInt32BE(substr($this->_data, 0, 3));
    $this->_flags = substr($this->_data, 3, 1);
    $this->_offset = Transform::getInt32BE(substr($this->_data, 4, 4));
  }

  /**
   * Returns the buffer size.
   * 
   * @return integer
   */
  public function getSize() { return $this->_size; }

  /**
   * Checks whether or not the flag is set. Returns <var>true</var> if the flag
   * is set, <var>false</var> otherwise.
   * 
   * @param integer $flag The flag to query.
   * @return boolean
   */
  public function hasFlag($flag) { return ($this->_flags & $flag) == $flag; }
  
  /**
   * Returns the offset to next tag.
   * 
   * @return integer
   */
  public function getOffset() { return $this->_size; }
}
