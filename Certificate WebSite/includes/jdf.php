<?php

function gregorian_to_jalali($gy,$gm,$gd,$mod='-'){
    $g_d_m = array(0,31,59,90,120,151,181,212,243,273,304,334);
    if($gy>1600){
        $jy=979;
        $gy-=1600;
    }else{
        $jy=0;
        $gy-=621;
    }

    $gy2 = ($gm > 2)?($gy+1):$gy;
    $days = (365*$gy) + intval(($gy2+3)/4) - intval(($gy2+99)/100)
          + intval(($gy2+399)/400) - 80 + $gd + $g_d_m[$gm-1];

    $jy += 33*intval($days/12053);
    $days %= 12053;

    $jy += 4*intval($days/1461);
    $days %= 1461;

    if($days > 365){
        $jy += intval(($days-1)/365);
        $days = ($days-1)%365;
    }

    if($days < 186){
        $jm = 1 + intval($days/31);
        $jd = 1 + ($days%31);
    }else{
        $jm = 7 + intval(($days-186)/30);
        $jd = 1 + (($days-186)%30);
    }

    return $jy.$mod.$jm.$mod.$jd;
}

function jalali_to_gregorian($jy,$jm,$jd,$mod='-'){
    $jy += 1595;
    $days = -355668 + (365*$jy) + (intval($jy/33)*8) + intval(((($jy%33)+3)/4)) + $jd;

    if($jm < 7){
        $days += ($jm-1)*31;
    }else{
        $days += (($jm-7)*30) + 186;
    }

    $gy = 400 * intval($days/146097);
    $days %= 146097;

    if($days > 36524){
        $gy += 100 * intval(--$days/36524);
        $days %= 36524;
        if($days >= 365) $days++;
    }

    $gy += 4 * intval($days/1461);
    $days %= 1461;

    if($days > 365){
        $gy += intval(($days-1)/365);
        $days = ($days-1)%365;
    }

    $gd = $days + 1;

    $sal_a = array(0,31,($gy%4==0 && $gy%100!=0 || $gy%400==0)?29:28,
        31,30,31,30,31,31,30,31,30,31);

    for($gm=1;$gm<=12;$gm++){
        if($gd <= $sal_a[$gm]){
            break;
        }
        $gd -= $sal_a[$gm];
    }

    return $gy.$mod.$gm.$mod.$gd;
}