<table>
    <tbody>
        <tr>
            <th>Versão do Spaghetti*</th>
            <td><?php echo SPAGHETTI_VERSION ?></td>
        </tr>
        <tr>
            <th>Ambiente</th>
            <td><?php echo Config::read("environment") ?></td>
        </tr>
        <tr>
            <th>Versão do PHP</th>
            <td><?php echo phpversion() ?></td>
        </tr>
		<?php if(function_exists("apache_get_version")): ?>
        <tr>
            <th>Servidor</th>
            <td><?php echo apache_get_version() ?></td>
        </tr>
		<?php endif ?>
        <tr>
            <th>Caminho raiz</th>
            <td><?php echo SPAGHETTI_ROOT ?></td>
        </tr>
		
    </tbody>
</table>