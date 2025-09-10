<section class="container btn-convenio">
	<div>
                <?php
                require_once __DIR__ . '/admin/database.php';
                $pdo = Database::connect();
                $id = 9;
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = "SELECT id,parametro,valor FROM parametros WHERE id = ? ";
                $q = $pdo->prepare($sql);
                $q->execute([$id]);
                $data = $q->fetch(PDO::FETCH_ASSOC);

                Database::disconnect();

                $valor = $data['valor'] ?? '';
                $fileUrl = 'admin/files/' . $valor;
                $filePath = __DIR__ . '/' . $fileUrl;
                $fileExists = $valor && file_exists($filePath);
                ?>
                <a href="<?= $fileExists ? $fileUrl : '#' ?>" <?= $fileExists ? 'target="_blank"' : 'class="disabled" title="Archivo no disponible" onclick="return false;"' ?>>Ver convenio</a>
                <a href="turnos.php" class="segundo-enlace">Sacar turno para vender</a>
                <a href="admin/loginProveedores.php" target="_blank">Ver el estado de mis ventas</a>
        </div>
</section>