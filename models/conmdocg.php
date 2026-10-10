<?php
class Conmdocg {

    private $iddocg;
    private $ndocasp;
    private $tipodoc;
    private $nomarchivo;
    private $rutarchivo;
    private $feccar;
    private $estdoc;
    private $obsdoc;

    public function getIddocg() {
        return $this->iddocg;
    }
    public function getNdocasp() {
        return $this->ndocasp;
    }
    public function getTipodoc() {
        return $this->tipodoc;
    }
    public function getNomarchivo() {
        return $this->nomarchivo;
    }
    public function getRutarchivo() {
        return $this->rutarchivo;
    }
    public function getFeccar() {
        return $this->feccar;
    }
    public function getEstdoc() {
        return $this->estdoc;
    }
    public function getObsdoc() {
        return $this->obsdoc;
    }

    public function setIddocg($iddocg) {
        $this->iddocg = $iddocg;
    }
    public function setNdocasp($ndocasp) {
        $this->ndocasp = $ndocasp;
    }
    public function setTipodoc($tipodoc) {
        $this->tipodoc = $tipodoc;
    }
    public function setNomarchivo($nomarchivo) {
        $this->nomarchivo = $nomarchivo;
    }
    public function setRutarchivo($rutarchivo) {
        $this->rutarchivo = $rutarchivo;
    }
    public function setFeccar($feccar) {
        $this->feccar = $feccar;
    }
    public function setEstdoc($estdoc) {
        $this->estdoc = $estdoc;
    }
    public function setObsdoc($obsdoc) {
        $this->obsdoc = $obsdoc;
    }

    public function getDocsByNdoc($ndocasp) {
        $sql = "SELECT dg.*, v.nomval AS nomtipodoc 
                FROM docasp AS dg 
                LEFT JOIN valor AS v ON dg.tipodoc = v.idval 
                WHERE dg.ndocasp = :ndocasp 
                ORDER BY dg.feccar DESC";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":ndocasp", $ndocasp);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveOrUpdate() {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();

            $existing = $this->getOne();
            if ($existing) {
                $sql = "UPDATE docasp SET 
                        tipodoc = :tipodoc,
                        nomarchivo = :nomarchivo,
                        rutarchivo = :rutarchivo,
                        feccar = :feccar,
                        estdoc = :estdoc,
                        obsdoc = :obsdoc
                        WHERE iddocg = :iddocg";
                $result = $conexion->prepare($sql);
                $result->bindParam(":iddocg", $existing['iddocg']);
            } else {
                $sql = "INSERT INTO docasp (ndocasp, tipodoc, nomarchivo, rutarchivo, feccar, estdoc, obsdoc) 
                        VALUES (:ndocasp, :tipodoc, :nomarchivo, :rutarchivo, :feccar, :estdoc, :obsdoc)";
                $result = $conexion->prepare($sql);
                $result->bindParam(":ndocasp", $this->ndocasp);
            }

            $result->bindParam(":tipodoc", $this->tipodoc);
            $result->bindParam(":nomarchivo", $this->nomarchivo);
            $result->bindParam(":rutarchivo", $this->rutarchivo);
            $result->bindParam(":feccar", $this->feccar);
            $result->bindParam(":estdoc", $this->estdoc);
            $result->bindParam(":obsdoc", $this->obsdoc);

            $result->execute();
            return true;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), '1062') !== false) {
                return 'duplicate';
            }
            throw $e;
        }
    }

    public function getOne() {
        $sql = "SELECT * FROM docasp WHERE ndocasp = :ndocasp AND tipodoc = :tipodoc";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":ndocasp", $this->ndocasp);
        $result->bindParam(":tipodoc", $this->tipodoc);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function getTiposDocumento() {
        $sql = "SELECT idval, nomval, parval FROM valor WHERE iddom IN (11, 27) AND act = 1 ORDER BY iddom, nomval";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->execute();
        $all = $result->fetchAll(PDO::FETCH_ASSOC);

        $categorized = [
            'certificados_academicos' => [
                'nombre' => 'Certificados Académicos',
                'tipos' => []
            ],
            'documentos_identificacion' => [
                'nombre' => 'Documentos de Identificación',
                'tipos' => []
            ],
            'actas_compromiso' => [
                'nombre' => 'Actas de Compromiso del Aprendiz',
                'tipos' => []
            ],
            'documentacion_complementaria' => [
                'nombre' => 'Documentación Complementaria',
                'tipos' => []
            ],
            'documentos_extranjeros' => [
                'nombre' => 'Documentos para Extranjeros',
                'tipos' => []
            ]
        ];

        $mapeo = [
            1801 => ['cat' => 'documentos_identificacion', 'nom' => 'Cédula de ciudadanía'],
            1802 => ['cat' => 'documentos_identificacion', 'nom' => 'Tarjeta de identidad'],
            1803 => ['cat' => 'documentos_identificacion', 'nom' => 'Cédula de extranjería vigente'],
            1804 => ['cat' => 'documentos_extranjeros', 'nom' => 'Pasaporte'],
            1805 => ['cat' => 'documentos_extranjeros', 'nom' => 'Permiso Temporal de Permanencia (PPT)'],
            2101 => ['cat' => 'certificados_academicos', 'nom' => 'Diploma de bachillerato original'],
            2102 => ['cat' => 'certificados_academicos', 'nom' => 'Acta de grado'],
            2103 => ['cat' => 'certificados_academicos', 'nom' => 'Resultados prueba Saber 11'],
            2104 => ['cat' => 'documentacion_complementaria', 'nom' => 'Certificado de afiliación a salud (EPS/Sisbén)'],
            2105 => ['cat' => 'documentacion_complementaria', 'nom' => 'Dos fotografías recientes (3x4, fondo blanco)'],
            2106 => ['cat' => 'actas_compromiso', 'nom' => 'Acta de compromiso menor de edad'],
            2107 => ['cat' => 'actas_compromiso', 'nom' => 'Acta de compromiso mayor de edad'],
        ];

        $foundIds = [];
        foreach ($all as $item) {
            if (isset($mapeo[$item['idval']])) {
                $cat = $mapeo[$item['idval']]['cat'];
                $categorized[$cat]['tipos'][] = [
                    'idval' => $item['idval'],
                    'nomval' => $mapeo[$item['idval']]['nom']
                ];
                $foundIds[$item['idval']] = true;
            }
        }

        if (!isset($foundIds[2106])) {
            $categorized['actas_compromiso']['tipos'][] = [
                'idval' => 2106,
                'nomval' => 'Acta de compromiso menor de edad'
            ];
        }
        if (!isset($foundIds[2107])) {
            $categorized['actas_compromiso']['tipos'][] = [
                'idval' => 2107,
                'nomval' => 'Acta de compromiso mayor de edad'
            ];
        }

        return $categorized;
    }

    public function getAllTiposDocumentoFlat() {
        $categorized = $this->getTiposDocumento();
        $flat = [];
        foreach ($categorized as $cat) {
            foreach ($cat['tipos'] as $tipo) {
                $flat[] = $tipo;
            }
        }
        return $flat;
    }

    public function validarTipoDocumento($tipodoc) {
        $tiposValidos = $this->getAllTiposDocumentoFlat();
        foreach ($tiposValidos as $t) {
            if ($t['idval'] == $tipodoc) {
                return true;
            }
        }
        return false;
    }

    public function esDocumentoInvalido($tipodoc) {
        $tiposInvalidos = [1806, 1807, 1808];
        return in_array($tipodoc, $tiposInvalidos);
    }

    public function delete($iddocg) {
        $sql = "DELETE FROM docasp WHERE iddocg = :iddocg";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":iddocg", $iddocg);
        $result->execute();
    }

    public function getOneById($iddocg) {
        $sql = "SELECT * FROM docasp WHERE iddocg = :iddocg";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":iddocg", $iddocg);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function getUsuarioByDoc($ndocasp) {
        $sql = "SELECT u.idusu, u.ndocusu, u.nomusu, u.emausu, u.telcan, p.nomper 
                FROM usuario AS u 
                LEFT JOIN perfil AS p ON u.idper = p.idper 
                WHERE u.ndocusu = :ndocasp";
        $modelo = new conexion();
        $conexion = $modelo->get_conexion();
        $result = $conexion->prepare($sql);
        $result->bindParam(":ndocasp", $ndocasp);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function getAspirante($ndocasp) {
        return $this->getUsuarioByDoc($ndocasp);
    }

    public function getDocumentos($idusu_or_ndoc) {
        return $this->getDocsByNdoc($idusu_or_ndoc);
    }

    public function getTiposDoc() {
        return $this->getAllTiposDocumentoFlat();
    }

    public function getEstadisticasAspirantes() {
        try {
            $sql = "SELECT 
                        COUNT(u.idusu) AS total,
                        COALESCE(SUM(d.cnt), 0) AS total_docs,
                        COALESCE(SUM(CASE WHEN u.tdousu IN (1803,1804,1805) THEN 1 ELSE 0 END), 0) AS extranjeros,
                        COALESCE(SUM(CASE WHEN d.cnt >= IF(u.tdousu IN (1803,1804,1805), 8, 6) THEN 1 ELSE 0 END), 0) AS completos,
                        COALESCE(SUM(CASE WHEN d.cnt > 0 THEN 1 ELSE 0 END), 0) AS con_docs
                    FROM usuario AS u
                    LEFT JOIN (
                        SELECT ndocasp, COUNT(*) AS cnt
                        FROM docasp
                        WHERE estdoc = 1
                        GROUP BY ndocasp
                    ) AS d ON d.ndocasp = u.ndocusu
                    WHERE u.idper IN (3, 4)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $fila = $result->fetch(PDO::FETCH_ASSOC);

            $total = (int)($fila['total'] ?? 0);
            $totalDocs = (int)($fila['total_docs'] ?? 0);
            $completos = (int)($fila['completos'] ?? 0);
            $conDocs = (int)($fila['con_docs'] ?? 0);

            return [
                'total' => $total,
                'total_docs' => $totalDocs,
                'completos' => $completos,
                'faltan' => $total - $completos,
                'con_docs' => $conDocs,
                'sin_docs' => $total - $conDocs,
                'extranjeros' => (int)($fila['extranjeros'] ?? 0),
                'promedio' => $total > 0 ? round($totalDocs / $total, 1) : 0,
            ];
        } catch (Exception $e) {
            error_log("Error en getEstadisticasAspirantes: " . $e->getMessage());
            return ['total' => 0, 'total_docs' => 0, 'completos' => 0, 'faltan' => 0, 'con_docs' => 0, 'sin_docs' => 0, 'extranjeros' => 0, 'promedio' => 0];
        }
    }

    public function getAspirantesConDocsCount() {
        try {
            $sql = "SELECT COUNT(DISTINCT d.ndocasp) AS total_con_docs
                    FROM docasp d
                    INNER JOIN usuario u ON u.ndocusu = d.ndocasp
                    WHERE d.estdoc = 1 AND u.idper IN (3, 4)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $fila = $result->fetch(PDO::FETCH_ASSOC);
            return (int)($fila['total_con_docs'] ?? 0);
        } catch (Exception $e) {
            error_log("Error en getAspirantesConDocsCount: " . $e->getMessage());
            return 0;
        }
    }
}