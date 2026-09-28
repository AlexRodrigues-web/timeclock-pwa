<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Store
{
    public static function allActive(): array
    {
        $sql = "SELECT id, brand, name, city, district FROM stores WHERE active = 1 ORDER BY district, city, brand, name";
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function create(array $data): int
    {
        $sql = "
            INSERT INTO stores (brand, name, city, district, active)
            VALUES (:brand, :name, :city, :district, 1)
        ";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'brand' => $data['brand'],
            'name' => $data['name'],
            'city' => $data['city'],
            'district' => $data['district'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function seedNorthBase(): void
    {
        $stores = [
            ['Continente', 'Continente Coimbra Shopping', 'Coimbra', 'Coimbra'],
            ['Continente Modelo', 'Continente Modelo Coimbra - Eiras', 'Coimbra', 'Coimbra'],
            ['Continente Bom Dia', 'Continente Bom Dia Coimbra', 'Coimbra', 'Coimbra'],
            ['Continente Bom Dia', 'Continente Bom Dia Condeixa', 'Condeixa-a-Nova', 'Coimbra'],
            ['Continente Bom Dia', 'Continente Bom Dia Buarcos', 'Figueira da Foz', 'Coimbra'],
            ['Continente', 'Continente Aveiro', 'Aveiro', 'Aveiro'],
            ['Continente Bom Dia', 'Continente Bom Dia Aveiro', 'Aveiro', 'Aveiro'],
            ['Continente Bom Dia', 'Continente Bom Dia Avanca', 'Avanca', 'Aveiro'],
            ['Continente Bom Dia', 'Continente Bom Dia Estarreja', 'Estarreja', 'Aveiro'],
            ['Continente Bom Dia', 'Continente Bom Dia Esmoriz', 'Esmoriz', 'Aveiro'],
            ['Continente Bom Dia', 'Continente Bom Dia Espinho', 'Espinho', 'Aveiro'],
            ['Continente Modelo', 'Continente Modelo Santa Maria da Feira', 'Santa Maria da Feira', 'Aveiro'],
            ['Continente Bom Dia', 'Continente Bom Dia Santa Maria de Lamas', 'Santa Maria de Lamas', 'Aveiro'],
            ['Continente', 'Continente São João da Madeira', 'São João da Madeira', 'Aveiro'],
            ['Continente Bom Dia', 'Continente Bom Dia São João da Madeira', 'São João da Madeira', 'Aveiro'],
            ['Continente Bom Dia', 'Continente Bom Dia São João de Ver', 'São João de Ver', 'Aveiro'],
            ['Continente Modelo', 'Continente Modelo Avintes', 'Avintes', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Boucinhas', 'Vila Nova de Gaia', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Canidelo', 'Vila Nova de Gaia', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Carvalhos', 'Pedroso', 'Porto'],
            ['Continente Modelo', 'Continente Modelo São Félix da Marinha', 'Vila Nova de Gaia', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Asprela', 'Porto', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Bom Sucesso', 'Porto', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Campo 24 de Agosto', 'Porto', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Cufra', 'Porto', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Sá da Bandeira', 'Porto', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Senhora da Luz', 'Porto', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Serpa Pinto', 'Porto', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Via Catarina Shopping', 'Porto', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Via Rápida', 'Porto', 'Porto'],
            ['Continente Modelo', 'Continente Modelo Ermesinde', 'Ermesinde', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Ermesinde', 'Ermesinde', 'Porto'],
            ['Continente Modelo', 'Continente Modelo São Cosme', 'Gondomar', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia São Pedro da Cova', 'São Pedro da Cova', 'Porto'],
            ['Continente Modelo', 'Continente Modelo Fânzeres', 'Fânzeres', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Baguim do Monte', 'Baguim do Monte', 'Porto'],
            ['Continente', 'Continente Valongo', 'Valongo', 'Porto'],
            ['Continente Bom Dia', 'Continente Bom Dia Cabeça Santa', 'Penafiel', 'Porto'],
            ['Continente Modelo', 'Continente Modelo Santo Tirso', 'Santo Tirso', 'Porto'],
            ['Continente Modelo', 'Continente Modelo Barcelos', 'Barcelos', 'Braga'],
            ['Continente Bom Dia', 'Continente Bom Dia Barcelos', 'Barcelos', 'Braga'],
            ['Continente Modelo', 'Continente Modelo Braga', 'Braga', 'Braga'],
            ['Continente', 'Continente Braga', 'Braga', 'Braga'],
            ['Continente', 'Continente Braga Nova Arcada', 'Braga', 'Braga'],
            ['Continente Bom Dia', 'Continente Bom Dia Braga - Quinta das Portas', 'Braga', 'Braga'],
            ['Continente Bom Dia', 'Continente Bom Dia Braga Oficina São José', 'Braga', 'Braga'],
            ['Continente Bom Dia', 'Continente Bom Dia Celeirós', 'Braga', 'Braga'],
            ['Continente Bom Dia', 'Continente Bom Dia Delães', 'Vila Nova de Famalicão', 'Braga'],
            ['Continente Bom Dia', 'Continente Bom Dia Cabeceiras de Basto', 'Cabeceiras de Basto', 'Braga'],
            ['Continente Bom Dia', 'Continente Bom Dia Caldas das Taipas', 'Caldelas', 'Braga'],
            ['Continente Modelo', 'Continente Modelo Esposende', 'Esposende', 'Braga'],
            ['Continente Bom Dia', 'Continente Bom Dia Darque', 'Darque', 'Viana do Castelo'],
            ['Continente Modelo', 'Continente Modelo Chaves', 'Chaves', 'Vila Real'],
            ['Continente Bom Dia', 'Continente Bom Dia Chaves', 'Chaves', 'Vila Real'],
            ['Continente Modelo', 'Continente Modelo Bragança', 'Bragança', 'Bragança'],
            ['Continente Bom Dia', 'Continente Bom Dia Castro Daire', 'Castro Daire', 'Viseu'],
            ['Continente Bom Dia', 'Continente Bom Dia Canas de Senhorim', 'Canas de Senhorim', 'Viseu'],
            ['Continente Bom Dia', 'Continente Bom Dia Santa Comba Dão', 'Santa Comba Dão', 'Viseu'],
            ['Continente Bom Dia', 'Continente Bom Dia Sátão', 'Sátão', 'Viseu'],
            ['Continente Bom Dia', 'Continente Bom Dia Seia', 'Seia', 'Guarda'],
            ['Continente', 'Continente Covilhã', 'Covilhã', 'Castelo Branco'],
            ['Continente Bom Dia', 'Continente Bom Dia Castelo Branco', 'Castelo Branco', 'Castelo Branco'],
            ['Continente Modelo', 'Continente Modelo Castelo Branco', 'Castelo Branco', 'Castelo Branco']
        ];

        $sql = "
            INSERT INTO stores (brand, name, city, district, active)
            VALUES (:brand, :name, :city, :district, 1)
            ON DUPLICATE KEY UPDATE
                city = VALUES(city),
                district = VALUES(district),
                active = VALUES(active)
        ";

        $stmt = Database::connection()->prepare($sql);

        foreach ($stores as $store) {
            $stmt->execute([
                'brand' => $store[0],
                'name' => $store[1],
                'city' => $store[2],
                'district' => $store[3],
            ]);
        }
    }
}
