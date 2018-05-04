<?php
class MF_Helpers {
    public function setDate($date){
        $old = new DateTime($date); 
        return $old->format("Y-m-d H:i:s"); 
    }
}
