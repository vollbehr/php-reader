<?php

declare(strict_types=1);

namespace Vollbehr\Media;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This class represents a file containing ID3v1 headers as described in
 * {@see http://www.id3.org/id3v2-00 The ID3-Tag Specification Appendix}.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Id3v1
{
    private string $_title;

    private string $_artist;

    private string $_album;

    private string $_year;

    private string $_comment;

    /** @var integer */
    private $_track;

    private int $_genre;

    /**
     * The genre list.
     * @var Array
     */
    public static $genres = ['Blues', 'Classic Rock', 'Country', 'Dance', 'Disco', 'Funk', 'Grunge',
         'Hip-Hop', 'Jazz', 'Metal', 'New Age', 'Oldies', 'Other', 'Pop', 'R&B',
         'Rap', 'Reggae', 'Rock', 'Techno', 'Industrial', 'Alternative', 'Ska',
         'Death Metal', 'Pranks', 'Soundtrack', 'Euro-Techno', 'Ambient',
         'Trip-Hop', 'Vocal', 'Jazz+Funk', 'Fusion', 'Trance', 'Classical',
         'Instrumental', 'Acid', 'House', 'Game', 'Sound Clip', 'Gospel',
         'Noise', 'AlternRock', 'Bass', 'Soul', 'Punk', 'Space', 'Meditative',
         'Instrumental Pop', 'Instrumental Rock', 'Ethnic', 'Gothic',
         'Darkwave', 'Techno-Industrial', 'Electronic', 'Pop-Folk', 'Eurodance',
         'Dream', 'Southern Rock', 'Comedy', 'Cult', 'Gangsta', 'Top 40',
         'Christian Rap', 'Pop/Funk', 'Jungle', 'Native American', 'Cabaret',
         'New Wave', 'Psychadelic', 'Rave', 'Showtunes', 'Trailer', 'Lo-Fi',
         'Tribal', 'Acid Punk', 'Acid Jazz', 'Polka', 'Retro', 'Musical',
         'Rock & Roll', 'Hard Rock', 'Folk', 'Folk-Rock', 'National Folk',
         'Swing', 'Fast Fusion', 'Bebob', 'Latin', 'Revival', 'Celtic',
         'Bluegrass', 'Avantgarde', 'Gothic Rock', 'Progressive Rock',
         'Psychedelic Rock', 'Symphonic Rock', 'Slow Rock', 'Big Band',
         'Chorus', 'Easy Listening', 'Acoustic', 'Humour', 'Speech', 'Chanson',
         'Opera', 'Chamber Music', 'Sonata', 'Symphony', 'Booty Bass', 'Primus',
         'Porn Groove', 'Satire', 'Slow Jam', 'Club', 'Tango', 'Samba',
         'Folklore', 'Ballad', 'Power Ballad', 'Rhythmic Soul', 'Freestyle',
         'Duet', 'Punk Rock', 'Drum Solo', 'A capella', 'Euro-House',
         'Dance Hall', 255 => 'Unknown'];

    private ?\Vollbehr\Io\FileReader $_reader;

    /** @var string */
    private $_filename;

    /**
     * Constructs the Id3v1 class with given file. The file is not mandatory
     * argument and may be omitted as a new tag can be written to a file also by
     * giving the filename to the {@see #write} method of this class.
     * @param string|resource|\Vollbehr\Io\Reader $filename The path to the file,
     *  file descriptor of an opened file, or a {@see \Vollbehr\Io\Reader} instance.
     * @throws Id3\Exception if given file descriptor is not valid
     */
    public function __construct($filename = null)
    {
        if ($filename === null) {
            return;
        }
        if ($filename instanceof \Vollbehr\Io\Reader) {
            $this->_reader = &$filename;
        } else {
            $this->_filename = $filename;

            try {
                $this->_reader = new \Vollbehr\Io\FileReader($filename);
            } catch (\Vollbehr\Io\Exception $e) {
                $this->_reader = null;

                throw new Id3\Exception($e->getMessage());
            }
        }

        if ($this->_reader->getSize() < 128) {
            $this->_reader = null;

            throw new Id3\Exception('File does not contain ID3v1 tag');
        }
        $this->_reader->setOffset(-128);
        if ($this->_reader->read(3) != 'TAG') {
            $this->_reader = null;

            throw new Id3\Exception('File does not contain ID3v1 tag');
        }

        $this->_title   = $this->_reader->readString8(30, " \0");
        $this->_artist  = $this->_reader->readString8(30, " \0");
        $this->_album   = $this->_reader->readString8(30, " \0");
        $this->_year    = $this->_reader->readString8(4);
        $this->_comment = $this->_reader->readString8(28);

        /* ID3v1.1 support for tracks */
        $v11_null  = $this->_reader->read(1);
        $v11_track = $this->_reader->read(1);
        if (ord($v11_null) == 0 && ord($v11_track) != 0) {
            $this->_track = ord($v11_track);
        } else {
            $this->_comment = rtrim($this->_comment . $v11_null . $v11_track, " \0");
        }

        $this->_genre = $this->_reader->readInt8();
    }

    /**
     * Returns the title field.
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Sets a new value for the title field. The field cannot exceed 30
     * characters in length.
     * @param string $title The title.
     */
    public function setTitle($title): void
    {
        $this->_title = $title;
    }

    /**
     * Returns the artist field.
     * @return string
     */
    public function getArtist()
    {
        return $this->_artist;
    }

    /**
     * Sets a new value for the artist field. The field cannot exceed 30
     * characters in length.
     * @param string $artist The artist.
     */
    public function setArtist($artist): void
    {
        $this->_artist = $artist;
    }

    /**
     * Returns the album field.
     * @return string
     */
    public function getAlbum()
    {
        return $this->_album;
    }

    /**
     * Sets a new value for the album field. The field cannot exceed 30
     * characters in length.
     * @param string $album The album.
     */
    public function setAlbum($album): void
    {
        $this->_album = $album;
    }

    /**
     * Returns the year field.
     * @return string
     */
    public function getYear()
    {
        return $this->_year;
    }

    /**
     * Sets a new value for the year field. The field cannot exceed 4
     * characters in length.
     * @param string $year The year.
     */
    public function setYear($year): void
    {
        $this->_year = $year;
    }

    /**
     * Returns the comment field.
     * @return string
     */
    public function getComment()
    {
        return $this->_comment;
    }

    /**
     * Sets a new value for the comment field. The field cannot exceed 30
     * characters in length.
     * @param string $comment The comment.
     */
    public function setComment($comment): void
    {
        $this->_comment = $comment;
    }

    /**
     * Returns the track field.
     * @since ID3v1.1
     * @return integer
     */
    public function getTrack()
    {
        return $this->_track;
    }

    /**
     * Sets a new value for the track field. By setting this field you enforce
     * the 1.1 version to be used.
     * @since ID3v1.1
     * @param integer $track The track number.
     */
    public function setTrack($track): void
    {
        $this->_track = $track;
    }

    /**
     * Returns the genre.
     * @return string
     */
    public function getGenre()
    {
        if (isset(self::$genres[$this->_genre])) {
            return self::$genres[$this->_genre];
        } else {
            return self::$genres[255];
        } // unknown
    }

    /**
     * Sets a new value for the genre field. The value may either be a numerical
     * code representing one of the genres, or its string variant.
     * The genre is set to unknown (code 255) in case the string is not found
     * from the static {@see $genres} array of this class.
     * @param integer $genre The genre.
     */
    public function setGenre($genre): void
    {
        if ((is_numeric($genre) && $genre >= 0 && $genre <= 255) ||
            ($genre = array_search($genre, self::$genres)) !== false) {
            $this->_genre = $genre;
        } else {
            $this->_genre = 255;
        } // unknown
    }

    /**
     * Writes the possibly altered ID3v1 tag back to the file where it was read.
     * If the class was constructed without a file name, one can be provided
     * here as an argument. Regardless, the write operation will override
     * previous tag information, if found.
     * @param string $filename The optional path to the file.
     * @throws Id3\Exception if there is no file to write the tag to
     */
    public function write($filename = null): void
    {
        if ($filename === null && ($filename = $this->_filename) === null) {
            throw new Id3\Exception('No file given to write the tag to');
        }

        try {
            $writer = new \Vollbehr\Io\FileWriter($filename);
            $offset = $writer->getSize();
            if ($this->_reader instanceof \Vollbehr\Io\FileReader) {
                $offset = -128;
            } else {
                $reader = new \Vollbehr\Io\Reader($writer->getFileDescriptor());
                $reader->setOffset(-128);
                if ($reader->read(3) == 'TAG') {
                    $offset = -128;
                }
            }
            $writer->setOffset($offset);
            $writer->writeString8('TAG')
                   ->writeString8(substr($this->_title, 0, 30), 30)
                   ->writeString8(substr($this->_artist, 0, 30), 30)
                   ->writeString8(substr($this->_album, 0, 30), 30)
                   ->writeString8(substr($this->_year, 0, 4), 4);
            if ($this->_track) {
                $writer->writeString8(substr($this->_comment, 0, 28), 28)
                       ->writeInt8(0)
                       ->writeInt8($this->_track);
            } else {
                $writer->writeString8(substr($this->_comment, 0, 30), 30);
            }
            $writer->writeInt8($this->_genre);
            $writer->flush();
        } catch (\Vollbehr\Io\Exception $e) {

            throw new Id3\Exception($e->getMessage());
        }

        $this->_filename = $filename;
    }

    /**
     * Removes the ID3v1 tag altogether.
     * @param string $filename The path to the file.
     */
    public static function remove($filename): void
    {
        $reader = new \Vollbehr\Io\FileReader($filename, 'r+b');
        $reader->setOffset(-128);
        if ($reader->read(3) == 'TAG') {
            ftruncate($reader->getFileDescriptor(), $reader->getSize() - 128);
        }
    }

    /**
     * Magic function so that $obj->value will work.
     * @param string $name The field name.
     */
    public function __get(string $name)
    {
        if (method_exists($this, 'get' . ucfirst(strtolower($name)))) {
            return call_user_func([$this, 'get' . ucfirst(strtolower($name))]);
        } else {

            throw new Id3\Exception('Unknown field: ' . $name);
        }
    }

    /**
     * Magic function so that assignments with $obj->value will work.
     * @param string $name  The field name.
     * @param string $value The field value.
     */
    public function __set(string $name, $value)
    {
        if (method_exists($this, 'set' . ucfirst(strtolower($name)))) {
            call_user_func([$this, 'set' . ucfirst(strtolower($name))], $value);
        } else {

            throw new Id3\Exception('Unknown field: ' . $name);
        }
    }
}
