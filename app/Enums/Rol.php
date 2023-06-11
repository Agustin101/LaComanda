<?php

enum Rol:string {
    case Socio = "SC";
    case Mozo = "MZ";

        public function valor()
    {
            return match ($this) {
                Rol::Socio => "SC",
                Rol::Mozo => "MZ"
            };
        }
}
