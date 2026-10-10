<?php
require_once 'models/mevs.php';

class MevsController {

    // Método para listar todas las entradas
    public function index() {
        $mevs = new Mevs();
        $result = $mevs->getAll();
        require_once 'views/mevs/index.php'; // Archivo de vista para listar todas las entradas
    }

    // Método para mostrar una entrada específica
    public function show($idses) {
        $mevs = new Mevs();
        $mevs->setIdses($idses);
        $result = $mevs->getOne();
        require_once 'views/mevs/show.php'; // Archivo de vista para mostrar una entrada específica
    }

    // Método para guardar una nueva entrada
    public function store() {
        $mevs = new Mevs();
        $mevs->setIdses($_POST['idses']);
        $mevs->setIdpage($_POST['idage']);
        $mevs->setFecses($_POST['fecses']);
        $mevs->setIdeva($_POST['ideva']);
        $mevs->setIdusu($_POST['idusu']);
        $mevs->setComeva($_POST['comeva']);
        $mevs->setValeva($_POST['valeva']);
        $mevs->save();
        header('Location: index.php?controller=mevs&action=index');
    }

    // Método para actualizar una entrada existente
    public function update($idses) {
        $mevs = new Mevs();
        $mevs->setIdses($idses);
        $mevs->setIdpage($_POST['idage']);
        $mevs->setFecses($_POST['fecses']);
        $mevs->setIdeva($_POST['ideva']);
        $mevs->setIdusu($_POST['idusu']);
        $mevs->setComeva($_POST['comeva']);
        $mevs->setValeva($_POST['valeva']);
        $mevs->edit();
        header('Location: index.php?controller=mevs&action=index');
    }

    // Método para eliminar una entrada
    public function delete($idses) {
        $mevs = new Mevs();
        $mevs->setIdses($idses);
        $mevs->del();
        header('Location: index.php?controller=mevs&action=index');
    }
}
?>
