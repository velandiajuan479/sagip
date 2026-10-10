<?php
/**
 * Modelo: Mbitacora (etambit.php)
 * Gestiona las operaciones de base de datos para las bitácoras de etapa productiva.
 */
class etambit {
    private $conexion;
    private $lastError = '';

    public function __construct($db) {
        $this->conexion = $db; // Asume que la conexión PDO se inyecta o se llama desde un singleton
    }

    public function getLastError() {
        return $this->lastError;
    }

    // -------------------------------------------------------------------------
    // MÉTODOS DE CONSULTA (GETTERS)
    // -------------------------------------------------------------------------

    public function getAprendizData($idusu) {
        $sql = "SELECT u.idusu, u.nomusu, u.ndocusu, u.telcan, u.emausu,
                   f.idfic, f.nomfic AS nombre_ficha,
                   f.codpro AS idprograma,
                   p.nompro AS nombre_programa
            FROM usuario u
            LEFT JOIN usufic uf ON uf.idusu = u.idusu AND uf.actfic = 1
            LEFT JOIN ficha f ON f.idfic = uf.idfic
            LEFT JOIN programa p ON p.codpro = f.codpro
            WHERE u.idusu = :idusu";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmpresaAprendiz($idusu) {
        $sql = "SELECT e.idemp, e.nomemp, e.numdocemp, e.diremp, e.telemp,
                       h.idfic, h.codpro
                FROM usufic uf
                INNER JOIN ficha f ON f.idfic = uf.idfic
                INNER JOIN hojatra h ON h.idfic = f.idfic
                INNER JOIN empresa e ON e.idemp = h.idemp
                WHERE uf.idusu = :idusu
                  AND uf.actfic = 1
                ORDER BY h.idnorad DESC
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getDominios() {
        $sql = "SELECT iddom, nomdom
            FROM dominio
            WHERE iddom IN (19, 20, 21, 22, 23, 24, 25)
            ORDER BY iddom";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubAlternativas($iddom = null) {
        $sql = "SELECT idval, nomval, iddom
                FROM valor
                WHERE act = 1
                  AND iddom IN (19, 20, 21, 22, 23, 24, 25)";
        if ($iddom) {
            $sql .= " AND iddom = :iddom";
        }
        $stmt = $this->conexion->prepare($sql);
        if ($iddom) {
            $stmt->bindParam(':iddom', $iddom, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPerfil($idusu) {
        $sql = "SELECT p.nomper
                FROM usuario u
                LEFT JOIN perfil p ON p.idper = u.idper
                WHERE u.idusu = :idusu
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':idusu', $idusu, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJefe($idjefe) {
        if (empty($idjefe)) {
            return [];
        }

        $sql = "SELECT idusu, nomusu, telcan, emausu
                FROM usuario
                WHERE idusu = :idjefe
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':idjefe', $idjefe, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNombresDominios() {
        return $this->getDominios();
    }

    public function getValoresPorDominio() {
        return $this->getSubAlternativas();
    }

    public function getAlternativaAndSubalternativa($idbitacora) {
        $sql = "SELECT d.nomdom AS alternativa, v.nomval AS subalternativa
                FROM bitacora b
                LEFT JOIN dominio d ON d.iddom = b.idaltep
                LEFT JOIN valor v ON v.idval = b.idsubaltep
                WHERE b.idbitacora = :idbitacora
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':idbitacora', $idbitacora, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAllBitacoras($idusu) {
        $sql = "SELECT b.idbitacora, b.numero_bitacora, b.fecha_inicio, b.fecha_fin, 
                       b.nombre_empresa, b.nit, b.estado, b.fecha_entrega,
                       b.`observación` AS observacion
                FROM bitacora b 
                WHERE b.idaprendiz = :idusu
                ORDER BY b.numero_bitacora ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOneBitacora($idbitacora, $idusu = null) {
        $sql = "SELECT * FROM bitacora WHERE idbitacora = :idbitacora";
        if ($idusu !== null) {
            $sql .= " AND idaprendiz = :idusu";
        }
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idbitacora', $idbitacora, PDO::PARAM_INT);
        if ($idusu !== null) {
            $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSiguienteNumeroBitacora($idusu) {
        $sql = "SELECT MAX(numero_bitacora) as max_num FROM bitacora WHERE idaprendiz = :idusu";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['max_num'] ? $result['max_num'] + 1 : 1);
    }

    // -------------------------------------------------------------------------
    // MÉTODOS DE TRANSACCIÓN (CRUD)
    // -------------------------------------------------------------------------

    private function resolveIdAprendiz(array $data) {
        if (!empty($data['idusu'])) {
            return (int) $data['idusu'];
        }
        if (!empty($data['idaprendiz'])) {
            return (int) $data['idaprendiz'];
        }
        return 0;
    }

    public function saveBitacora($data, $sessionUserId = null) {
        $this->lastError = '';

        try {
            $this->conexion->beginTransaction();
            $idAprendiz = $this->resolveIdAprendiz($data);

            if (empty($idAprendiz)) {
                throw new InvalidArgumentException('Falta la identidad del aprendiz.');
            }

            if ($sessionUserId === null || (int) $sessionUserId !== $idAprendiz) {
                throw new InvalidArgumentException('El aprendiz enviado no coincide con el usuario de la sesión activa.');
            }

            $userStmt = $this->conexion->prepare('SELECT idusu FROM usuario WHERE idusu = :idusu LIMIT 1');
            $userStmt->bindValue(':idusu', $idAprendiz, PDO::PARAM_INT);
            $userStmt->execute();
            if (!$userStmt->fetchColumn()) {
                throw new InvalidArgumentException('El aprendiz de la sesión no existe en la tabla usuario.');
            }

            if (!defined('BITACORA_MODO_DESARROLLO') || !BITACORA_MODO_DESARROLLO) {
                $this->validateOptionalUser($data['idjefe'] ?? null, 'jefe');
                $this->validateOptionalUser($data['idinstructor'] ?? null, 'instructor');
                $this->validateDomain($data['idaltep'] ?? null);
            }

            $data['idusu'] = $idAprendiz;

           if (empty($data['idbitacora'])) {
                // INSERTAR NUEVA BITÁCORA
                $sql = "INSERT INTO bitacora (
                            idaprendiz, idjefe, idinstructor, numero_bitacora, nombre_empresa, nit,
                            fecha_inicio, fecha_fin, observación, nvlarl, idaltep, idsubaltep,
                            archivo_evidencia, link_evidencia, estado, fecha_entrega
                        ) VALUES (
                            :idaprendiz, :idjefe, :idinstructor, :numero_bitacora, :nombre_empresa, :nit,
                            :fecha_inicio, :fecha_fin, :observacion, :nvlarl, :idaltep, :idsubaltep,
                            :archivo_evidencia, :link_evidencia, 'Pendiente', NOW()
                        )";
                $stmt = $this->conexion->prepare($sql);
           } else {
            // ACTUALIZAR BITÁCORA EXISTENTE
            $sql = "UPDATE bitacora SET 
                nombre_empresa = :nombre_empresa, 
                nit = :nit, 
                observación = :observacion,
                nvlarl = :nvlarl, 
                idaltep = :idaltep, 
                idsubaltep = :idsubaltep,
                archivo_evidencia = COALESCE(:archivo_evidencia, archivo_evidencia),
                link_evidencia = :link_evidencia,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin
            WHERE idbitacora = :idbitacora
              AND idaprendiz = :idaprendiz
              AND estado = 'Pendiente'";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':idbitacora', $data['idbitacora'], PDO::PARAM_INT);
            }
            // ✅ Vinculación de campos obligatorios para INSERT
            if (empty($data['idbitacora'])) {
                $stmt->bindParam(':idjefe', $data['idjefe'], PDO::PARAM_INT);
                $stmt->bindParam(':idinstructor', $data['idinstructor'], PDO::PARAM_INT);
                $stmt->bindParam(':numero_bitacora', $data['numero_bitacora'], PDO::PARAM_INT);
                }
            // ✅ Fechas vinculadas siempre (tanto para INSERT como para UPDATE)
            $stmt->bindParam(':fecha_inicio', $data['fecha_inicio']);
            $stmt->bindParam(':fecha_fin', $data['fecha_fin']);
            
            if (empty($data['idbitacora'])) {
                $stmt->bindParam(':idaprendiz', $data['idaprendiz'], PDO::PARAM_INT);
                $stmt->bindParam(':idjefe', $data['idjefe'], PDO::PARAM_INT);
                $stmt->bindParam(':idinstructor', $data['idinstructor'], PDO::PARAM_INT);
                $stmt->bindParam(':numero_bitacora', $data['numero_bitacora'], PDO::PARAM_INT);
                $stmt->bindParam(':fecha_inicio', $data['fecha_inicio']);
                $stmt->bindParam(':fecha_fin', $data['fecha_fin']);
            }

            $stmt->bindParam(':idaprendiz', $data['idusu'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre_empresa', $data['nombre_empresa']);
            $stmt->bindParam(':nit', $data['nit']);
            $stmt->bindParam(':observacion', $data['observacion']);
            $stmt->bindParam(':nvlarl', $data['nvlarl']);
            $stmt->bindParam(':idaltep', $data['idaltep'], PDO::PARAM_INT);
            $stmt->bindParam(':idsubaltep', $data['idsubaltep'], PDO::PARAM_INT);
            $stmt->bindParam(':archivo_evidencia', $data['archivo_evidencia']);
            $stmt->bindParam(':link_evidencia', $data['link_evidencia']);

            $stmt->execute();

            $idbitacora = empty($data['idbitacora']) ? $this->conexion->lastInsertId() : $data['idbitacora'];

            if (!empty($data['idbitacora'])) {
                $delSql = "DELETE FROM actividades WHERE idbitacora = :idbitacora";
                $delStmt = $this->conexion->prepare($delSql);
                $delStmt->bindParam(':idbitacora', $idbitacora, PDO::PARAM_INT);
                $delStmt->execute();
            }

            if (!empty($data['actividades']['fecha_inicio'])) {
                $sqlAct = "INSERT INTO actividades
                           (idbitacora, fecha_inicio_act, fecha_fin_act, evidencia_cumplimiento, descripcion_actividad)
                           VALUES (:idbitacora, :f_ini, :f_fin, :evidencia, :descripcion)";
                $stmtAct = $this->conexion->prepare($sqlAct);

                $actividadCount = count($data['actividades']['fecha_inicio']);
                for ($i = 0; $i < $actividadCount; $i++) {
                    if (!isset($data['actividades']['fecha_fin'][$i], $data['actividades']['evidencia'][$i], $data['actividades']['descripcion'][$i])) {
                        throw new InvalidArgumentException('Las actividades tienen campos incompletos.');
                    }
                    $stmtAct->bindParam(':idbitacora', $idbitacora, PDO::PARAM_INT);
                    $stmtAct->bindParam(':f_ini', $data['actividades']['fecha_inicio'][$i]);
                    $stmtAct->bindParam(':f_fin', $data['actividades']['fecha_fin'][$i]);
                    $stmtAct->bindParam(':evidencia', $data['actividades']['evidencia'][$i]);
                    $stmtAct->bindParam(':descripcion', $data['actividades']['descripcion'][$i]);
                    $stmtAct->execute();
                }
            }

            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            $this->lastError = $this->getSaveErrorMessage($e);
            error_log("Error guardando bitácora: {$this->lastError} | Detalle: " . $e->getMessage());
            return false;
        }
    }

    private function getSaveErrorMessage(Throwable $e) {
        if ($e instanceof InvalidArgumentException) {
            return $e->getMessage();
        }

        if ($e instanceof PDOException) {
            $sqlState = $e->errorInfo[0] ?? $e->getCode();
            $driverCode = $e->errorInfo[1] ?? null;

            if ($sqlState === '23000' && $driverCode === 1452) {
                return 'Uno de los usuarios o la alternativa seleccionada no existe. Verifique aprendiz, jefe, instructor y modalidad.';
            }
            if ($sqlState === '23000' && $driverCode === 1062) {
                return 'Ya existe una bitácora con esos datos. Verifique el número de bitácora y el NIT.';
            }
            if ($sqlState === '22001') {
                return 'Uno de los textos supera la longitud permitida por la base de datos.';
            }
            if ($sqlState === '22007') {
                return 'Una de las fechas no tiene un formato válido.';
            }
        }

        return 'No fue posible guardar la bitácora por un error de base de datos. Revise los campos y vuelva a intentarlo.';
    }

    private function validateOptionalUser($idusu, $tipo) {
        if ($idusu === null || $idusu === '') {
            return;
        }

        $stmt = $this->conexion->prepare('SELECT idusu FROM usuario WHERE idusu = :idusu LIMIT 1');
        $stmt->bindValue(':idusu', (int) $idusu, PDO::PARAM_INT);
        $stmt->execute();
        if (!$stmt->fetchColumn()) {
            throw new InvalidArgumentException("El usuario seleccionado como $tipo no existe. Déjelo vacío o seleccione uno válido.");
        }
    }

    private function validateDomain($iddom) {
        $allowedDomains = [19, 20, 21, 22, 23, 24, 25];
        if (!in_array((int) $iddom, $allowedDomains, true)) {
            throw new InvalidArgumentException('La alternativa de etapa productiva no es válida. Seleccione una de las siete opciones permitidas.');
        }

        $stmt = $this->conexion->prepare('SELECT iddom FROM dominio WHERE iddom = :iddom LIMIT 1');
        $stmt->bindValue(':iddom', (int) $iddom, PDO::PARAM_INT);
        $stmt->execute();
        if (!$stmt->fetchColumn()) {
            throw new InvalidArgumentException('La alternativa seleccionada no existe en la tabla dominio.');
        }
    }

    public function deleteBitacora($idbitacora, $idusu) {
        try {
            $this->conexion->beginTransaction();

            $sql = "DELETE FROM bitacora
                    WHERE idbitacora = :idbitacora
                      AND idaprendiz = :idusu
                      AND estado = 'Pendiente'";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':idbitacora', $idbitacora, PDO::PARAM_INT);
            $stmt->bindParam(':idusu', $idusu, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                $this->conexion->rollBack();
                return false;
            }

            $deleteActividades = "DELETE FROM actividades WHERE idbitacora = :idbitacora";
            $stmtActividades = $this->conexion->prepare($deleteActividades);
            $stmtActividades->bindParam(':idbitacora', $idbitacora, PDO::PARAM_INT);
            $stmtActividades->execute();

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error eliminando bitácora: " . $e->getMessage());
            return false;
        }
    }

    public function getActividadesByBitacora($idbitacora) {
        if (empty($idbitacora)) {
            return [];
        }

        $sql = "SELECT descripcion_actividad, fecha_inicio_act, fecha_fin_act, evidencia_cumplimiento
                FROM actividades
                WHERE idbitacora = :idbitacora
                ORDER BY fecha_inicio_act, idactividad";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':idbitacora', $idbitacora, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>