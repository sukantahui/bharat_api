<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoSlang implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If value is empty or not a string, nothing to check
        if (!is_string($value) || trim($value) === '') {
            return;
        }

        // Normalize unicode + lowercase for reliable matching
        $lowerValue = mb_strtolower($value, 'UTF-8');

        // Load bad words from config (fallback to an inline small list if config missing)
        $badWords = config('badwords.list', [
            // ===============================
            // 🌍 Common English Vulgarities
            // ===============================
            'fuck',
            'shit',
            'bitch',
            'asshole',
            'bastard',
            'dick',
            'cunt',
            'slut',
            'fag',
            'moron',
            'stupid',
            'idiot',
            'retard',
            'nigga',
            'whore',
            'jerk',
            'piss',
            'bloody',
            'suck',
            'dumb',
            'cock',
            'pussy',
            'balls',
            'boobs',
            'tits',
            'arse',
            'bollocks',
            'bugger',
            'crap',
            'damn',
            'prick',

            // ===============================
            // 💬 Internet Slang / Short Forms
            // ===============================
            'wtf',
            'lmfao',
            'lmao',
            'omfg',
            'stfu',
            'fml',
            'roflmao',
            'rofl',
            'af',
            'tf',
            'mf',
            'bastards',
            'fucker',
            'motherfucker',
            'fucked',
            'fucking',
            'fucktard',
            'shithead',
            'shitface',
            'shitbag',
            'dickhead',
            'jackass',
            'douche',
            'douchebag',
            'hoe',
            'thot',
            'trash',
            'loser',

            // ===============================
            // 🚫 Racial / Hate Slurs
            // ===============================
            'chink',
            'spic',
            'kike',
            'cracker',
            'coon',
            'wetback',
            'gook',
            'tranny',
            'dyke',
            'faggot',
            'queer',
            'retarded',
            'shemale',
            'negro',

            // ===============================
            // 🔞 Sexual / Explicit
            // ===============================
            'sex',
            'sexual',
            'porn',
            'porno',
            'pornographic',
            'nude',
            'naked',
            'jerkoff',
            'masturbate',
            'masturbation',
            'orgasm',
            'horny',
            'blowjob',
            'handjob',
            'suckmy',
            'deepthroat',
            'doggystyle',
            'cum',
            'ejaculate',
            'vagina',
            'penis',
            'anal',
            'anus',
            'clit',
            'dildo',
            'semen',
            'sperm',
            'boob',
            'breast',
            'milf',
            'xxx',
            '69',
            'bdsm',

            // ===============================
            // ⚔️ Insults / Aggression
            // ===============================
            'die',
            'kill',
            'hate',
            'ugly',
            'worthless',
            'garbage',
            'trash',
            'idiotic',
            'disgusting',
            'stupidass',
            'lazy',
            'fool',

            // ===============================
            // 🇮🇳 Hindi / Hinglish Slang (Romanized)
            // ===============================
            'madarchod',
            'bhenchod',
            'chutiya',
            'gaand',
            'randi',
            'suar',
            'harami',
            'kutte',
            'kutti',
            'kamina',
            'kamini',
            'nalayak',
            'gandu',
            'chakkar',
            'chakkas',
            'lund',
            'launda',
            'lavda',
            'chodna',
            'chodu',
            'behenchod',
            'maachod',
            'teri maa',
            'teri behen',
            'bakchod',
            'kuttey',
            'kuttiya',
            'kutta',
            'ullu',
            'ullu ka pattha',
            'chakkarwala',
            'rakhail',
            'bhosdike',
            'bhosda',
            'jhantu',
            'chakka',
            'rakhni',
            'tatte',
            'bhadwa',
            'bhadwe',
            'chinal',
            'raand',
            'maderchod',

            // ===============================
            // 🇮🇳 Hindi Words (Devanagari Script)
            // ===============================
            'मादरचोद',
            'बहनचोद',
            'चूतिया',
            'गांड',
            'रंडी',
            'कमीना',
            'कुत्ता',
            'कुत्ती',
            'हरामी',
            'भोसडीके',
            'लौड़ा',
            'लौंडा',
            'भोसड़ा',
            'भोसड़ी',
            'चुद',
            'चोद',
            'चोदना',
            'भड़वा',
            'भड़वे',
            'रांड',
            'गांडू',
            'चक्क',
            'चक्का',
            'सुअर',
            'नालायक',
            'पागल',
            'गधा',
            'गंदी',
            'नीच',
            'कमीने',
            'हरामखोर',

            // ===============================
            // 🇧🇩🇮🇳 Bengali / Banglish Slang
            // ===============================
            'chod',
            'chodna',
            'chodcho',
            'chodlam',
            'chodbo',
            'gandu',
            'haramjada',
            'haramjadi',
            'banchod',
            'magi',
            'meyechod',
            'rand',
            'randi',
            'kutta',
            'kuttar baccha',
            'bosdike',
            'bapchod',
            'chodachudi',
            'chudir',
            'chodon',
            'chodona',
            'chodbo',
            'bokachoda',
            'madarchod',
            'banchoda',
            'harami',
            'bosdi',
            'bosdike',
            'gand',
            'gandu',
            'fuckir',
            'randimoni',
            'pagla',
            'magi',
            'nicher manus',

            // ===============================
            // 🇧🇩 Bengali (বাংলা স্ক্রিপ্টে)
            // ===============================
            'চোদ',
            'চোদা',
            'চোদনা',
            'চুদ',
            'চুদি',
            'চুদছে',
            'চোদছো',
            'চুদি',
            'চোদাচুদি',
            'চোদন',
            'গান্ডু',
            'হারামজাদা',
            'বসদিকে',
            'রান্ডি',
            'রান্ডি',
            'মাগী',
            'বোকাচোদা',
            'চোদচুদি',
            'ভোসদিকে',
            'ভদ্দরলোক না',
            'চুতমারানি',
            'গান্ড',
            'গাধা',
            'পাগল',
            'বোকা',
            'নোংরা',
            'নিচু মানুষ',

            // ===============================
            // 💬 Variations & Leetspeak
            // ===============================
            'f*ck',
            'f@ck',
            'sh1t',
            'b!tch',
            'a$$',
            'f.u.c.k',
            's.h.i.t',
            'b!tch',
            'p0rn',
            's3x',
            'idi0t',
            'a$$hole',
            'd1ck',
            'f@ggot',
            'sl@t',
            'chuti@',
            'r@ndi',
            'bhench0d',
            'mad@rch0d',
            'bh0sdike',
            // ... you can keep a short fallback here if desired
        ]);

        foreach ($badWords as $word) {
            // skip empty config entries
            if ($word === '' || $word === null) {
                continue;
            }

            // Normalize each word as well
            $checkWord = mb_strtolower($word, 'UTF-8');

            if ($checkWord !== '' && str_contains($lowerValue, $checkWord)) {
                $fail("The {$attribute} contains inappropriate language. Please rephrase it.");
                return;
            }
        }
    }
}
