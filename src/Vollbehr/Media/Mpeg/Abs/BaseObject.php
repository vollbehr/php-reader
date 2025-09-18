<?php

declare(strict_types=1);

namespace Vollbehr\Media\Mpeg\Abs;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The base class for all MPEG Audio Bit Stream objects.
 * @author Ryan Butterfield
 * @author Sven Vollbehr
 */
abstract class BaseObject extends \Vollbehr\Media\Mpeg\BaseObject
{
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const VERSION_ONE = 3;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const VERSION_TWO = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const VERSION_TWO_FIVE = 0;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SAMPLING_FREQUENCY_LOW = 0;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SAMPLING_FREQUENCY_HIGH = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const LAYER_ONE = 3;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const LAYER_TWO = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const LAYER_THREE = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const CHANNEL_STEREO = 0;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const CHANNEL_JOINT_STEREO = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const CHANNEL_DUAL_CHANNEL = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const CHANNEL_SINGLE_CHANNEL = 3;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_SUBBAND_4_TO_31 = 0;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_SUBBAND_8_TO_31 = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_SUBBAND_12_TO_31 = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_SUBBAND_16_TO_31 = 3;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_ISOFF_MSSOFF = 0;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_ISON_MSSOFF = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_ISOFF_MSSON = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_ISON_MSSON = 3;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const EMPHASIS_NONE = 0;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const EMPHASIS_50_15 = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const EMPHASIS_CCIT_J17 = 3;
    /**
     * Layer III side information size lookup table.  The table has the
     * following format.
     * <code>
     * array (
     *   SAMPLING_FREQUENCY_HIGH | SAMPLING_FREQUENCY_LOW => array (
     *     CHANNEL_STEREO | CHANNEL_JOINT_STEREO | CHANNEL_DUAL_CHANNEL |
     *       CHANNEL_SINGLE_CHANNEL => <size>
     *   )
     * )
     * </code>
     * @var Array
     */
    protected static $sidesizes = [
        self::SAMPLING_FREQUENCY_HIGH => [
            self::CHANNEL_STEREO => 32,
            self::CHANNEL_JOINT_STEREO => 32,
            self::CHANNEL_DUAL_CHANNEL => 32,
            self::CHANNEL_SINGLE_CHANNEL => 17,
        ],
        self::SAMPLING_FREQUENCY_LOW => [
            self::CHANNEL_STEREO => 17,
            self::CHANNEL_JOINT_STEREO => 17,
            self::CHANNEL_DUAL_CHANNEL => 17,
            self::CHANNEL_SINGLE_CHANNEL => 9,
        ],
    ];

    /**
     * Constructs the class with given parameters.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
    }
}
