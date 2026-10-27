<?php

namespace App\Enums;

enum ReportReason: string
{
    case Spam = 'spam';
    case Harassment = 'harassment';
    case HateSpeech = 'hate_speech';
    case Violence = 'violence';
    case SexualContent = 'sexual_content';
    case FalseInformation = 'false_information';
    case Other = 'other';
}
