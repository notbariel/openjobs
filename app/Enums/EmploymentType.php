<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static FullTime()
 * @method static static PartTime()
 * @method static static Contractor()
 * @method static static Temporary()
 * @method static static Internship()
 * @method static static Volunteer()
 */
final class EmploymentType extends Enum
{
    const FullTime =   0;
    const PartTime =   1;
    const Contractor = 2;
    const Temporary =  3;
    const Internship = 4;
    const Volunteer =  5;
}
