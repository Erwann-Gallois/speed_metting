<?php

namespace App\Services;

use App\Entity\Variable;
use Doctrine\Persistence\ManagerRegistry;

class FonctionUtile
{
    public function __construct(private ManagerRegistry $doctrine)
    {
        
    }
    public function slotRDV(int $session):array
    {
        $slots = [];
        $sessions = [
            1 => ['14:00', '15:00'],
            2 => ['16:00', '17:00'],
        ];
        list($start, $end) = $sessions[$session];
        $start = new \DateTime($start);
        $end = new \DateTime($end);
        $i = 0;
        while ($start < $end) {
            $time = $start->format('H:i');
            $slots[$i] = $time;
            $start->modify('+10 minutes');
            $i++;
        }
        return $slots;
    }

    public function getMaxPlaceRDV(int $session):int
    {
        $variable = $this->doctrine->getRepository(Variable::class)->find(1);
        if ($session == 1) {
            return $variable->getPlaceRdv();
        }
        else {
            return $variable->getPlaceRdv2();
        }
    }  
}