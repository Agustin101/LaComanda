<?php

enum Sector : string{
    case Administracion = "ADM";
    case AtencionClientes = "AC";

    public function valor(){
        return match($this){
            Sector::Administracion => "ADM",
            Sector::AtencionClientes => "AC"
        };
    }
}