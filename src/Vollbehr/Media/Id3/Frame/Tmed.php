<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3\Frame;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Media type_ frame describes from which media the sound originated.
 * This may be a text string or a reference to the predefined media types found
 * in the list below. Example: 'VID/PAL/VHS'.
 * <pre>
 *  DIG    Other digital media
 *    /A    Analogue transfer from media
 *  ANA    Other analogue media
 *    /WAC  Wax cylinder
 *    /8CA  8-track tape cassette
 *  CD     CD
 *    /A    Analogue transfer from media
 *    /DD   DDD
 *    /AD   ADD
 *    /AA   AAD
 *  LD     Laserdisc
 *  TT     Turntable records
 *    /33    33.33 rpm
 *    /45    45 rpm
 *    /71    71.29 rpm
 *    /76    76.59 rpm
 *    /78    78.26 rpm
 *    /80    80 rpm
 *  MD     MiniDisc
 *    /A    Analogue transfer from media
 *  DAT    DAT
 *    /A    Analogue transfer from media
 *    /1    standard, 48 kHz/16 bits, linear
 *    /2    mode 2, 32 kHz/16 bits, linear
 *    /3    mode 3, 32 kHz/12 bits, non-linear, low speed
 *    /4    mode 4, 32 kHz/12 bits, 4 channels
 *    /5    mode 5, 44.1 kHz/16 bits, linear
 *    /6    mode 6, 44.1 kHz/16 bits, 'wide track' play
 *  DCC    DCC
 *    /A    Analogue transfer from media
 *  DVD    DVD
 *    /A    Analogue transfer from media
 *  TV     Television
 *    /PAL    PAL
 *    /NTSC   NTSC
 *    /SECAM  SECAM
 *  VID    Video
 *    /PAL    PAL
 *    /NTSC   NTSC
 *    /SECAM  SECAM
 *    /VHS    VHS
 *    /SVHS   S-VHS
 *    /BETA   BETAMAX
 *  RAD    Radio
 *    /FM   FM
 *    /AM   AM
 *    /LW   LW
 *    /MW   MW
 *  TEL    Telephone
 *    /I    ISDN
 *  MC     MC (normal cassette)
 *    /4    4.75 cm/s (normal speed for a two sided cassette)
 *    /9    9.5 cm/s
 *    /I    Type I cassette (ferric/normal)
 *    /II   Type II cassette (chrome)
 *    /III  Type III cassette (ferric chrome)
 *    /IV   Type IV cassette (metal)
 *  REE    Reel
 *    /9    9.5 cm/s
 *    /19   19 cm/s
 *    /38   38 cm/s
 *    /76   76 cm/s
 *    /I    Type I cassette (ferric/normal)
 *    /II   Type II cassette (chrome)
 *    /III  Type III cassette (ferric chrome)
 *    /IV   Type IV cassette (metal)
 * </pre>
 * @author Sven Vollbehr
 */
final class Tmed extends \Vollbehr\Media\Id3\TextFrame
{
}
