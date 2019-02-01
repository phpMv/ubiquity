<?php
phpinfo ();
print_r ( get_loaded_extensions () );
echo 'Xdebug ', (extension_loaded ( 'xdebug' ) ? '' : 'non '), 'exists';