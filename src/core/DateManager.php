<?php
namespace Src\Core;

class DateManager {
    private string $date;
    private string $lang;
    private string $format;
    private string $dateformated;
    private int $timestamp;
    private array $replacements = [];

    private array $joursL = [
        'fr' => [
            1 =>'Lundi',2 => 'Mardi',3 =>'Mercredi',
            4 => 'Jeudi',5 => 'Vendredi',6 => 'Samedi',7 => 'Dimanche'
        ],
        'en' => [
            1 =>'Monday',2 => 'Tuesday',3 =>'Wednesday',
            4 => 'Thursday',5 => 'Friday',6 => 'Saturday',7 => 'Sunday'
        ]
    ];

    private array $mois = [
        'fr' => [
            1 => 'Janvier',2 => 'Février',3 => 'Mars',
            4 => 'Avril',5 => 'Mai',6 => 'Juin',
            7 => 'Juillet',8 => 'Août',9 => 'Septembre',
            10 => 'Octobre',11 => 'Novembre',12 => 'Décembre'
        ],
        'en' => [
            1 => 'January',2 => 'February',3 => 'March',
            4 => 'April',5 => 'May',6 => 'June',
            7 => 'July',8 => 'August',9 => 'September',
            10 => 'October',11 => 'November',12 => 'December'
        ]
    ];

    public function __construct(string $date = '', ?string $lang = null, ?string $format = 'd m Y') {
        $this->date = \str_replace('/','-',$date) ?: 'now';
        $this->timestamp = strtotime($this->date);
        if ($this->timestamp === false) 
            throw new \RuntimeException("Erreur : date invalide !");

        $this->lang = $lang ? strtolower($lang) : substr(setlocale(LC_TIME, 0), 0, 2);
        $this->lang = in_array($this->lang, ['fr','en']) ? $this->lang : 'fr';
        $this->format = $format ?? 'd m Y';

        $this->strContruct();
    }

    public function strContruct(): string {
        $jourNum = (int)date('N', $this->timestamp); // 1 = lundi
        $this->replacements = [
            'd' => date('d', $this->timestamp),
            'D' => $this->joursL[$this->lang][$jourNum],
            'j' => substr($this->joursL[$this->lang][$jourNum],0,3),
            'm' => $this->mois[$this->lang][(int)date('m', $this->timestamp)],
            'M' => substr($this->mois[$this->lang][(int)date('m', $this->timestamp)],0,3),
            'ms' => date('m', $this->timestamp),
            'Y' => date('Y', $this->timestamp),
            'y' => substr(date('Y', $this->timestamp), -2),
            'H' => date('H', $this->timestamp),
            'h' => ((int)date('H', $this->timestamp) % 12) ?: 12,
            'A' => date('A', $this->timestamp),
            'I' => date('i', $this->timestamp),
            'i' => date('i', $this->timestamp),
            'S' => date('s', $this->timestamp),
            's' => date('s', $this->timestamp),
        ];

        $this->dateformated = strtr($this->format, $this->replacements);
        return $this->dateformated;
    }

    public function get(): string {
        return $this->dateformated;
    }
}
