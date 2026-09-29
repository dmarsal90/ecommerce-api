<?php

return [

    'builder' => [

        'actions' => [

            'clone' => [
                'label' => 'Clonar',
            ],

            'add' => [

                'label' => 'Agregar a :label',

                'modal' => [

                    'heading' => 'Agregar a :label',

                    'actions' => [

                        'add' => [
                            'label' => 'Agregar',
                        ],

                    ],

                ],

            ],

            'add_between' => [

                'label' => 'Insertar entre bloques',

                'modal' => [

                    'heading' => 'Agregar a :label',

                    'actions' => [

                        'add' => [
                            'label' => 'Agregar',
                        ],

                    ],

                ],

            ],

            'delete' => [
                'label' => 'Eliminar',
            ],

            'edit' => [

                'label' => 'Editar',

                'modal' => [

                    'heading' => 'Editar bloque',

                    'actions' => [

                        'save' => [
                            'label' => 'Guardar cambios',
                        ],

                    ],

                ],

            ],

            'reorder' => [
                'label' => 'Mover',
            ],

            'move_down' => [
                'label' => 'Mover abajo',
            ],

            'move_up' => [
                'label' => 'Mover arriba',
            ],

            'collapse' => [
                'label' => 'Colapsar',
            ],

            'expand' => [
                'label' => 'Expandir',
            ],

            'collapse_all' => [
                'label' => 'Colapsar todo',
            ],

            'expand_all' => [
                'label' => 'Expandir todo',
            ],

        ],

    ],

    'checkbox_list' => [

        'actions' => [

            'deselect_all' => [
                'label' => 'Deseleccionar todo',
            ],

            'select_all' => [
                'label' => 'Seleccionar todo',
            ],

        ],

    ],

    'color_picker' => [

        'panel_label' => 'Selector de color',

    ],

    'date_time_picker' => [

        'month_select' => [
            'label' => 'Mes',
        ],

        'year_input' => [
            'label' => 'Año',
        ],

        'hour_input' => [
            'label' => 'Hora',
        ],

        'minute_input' => [
            'label' => 'Minuto',
        ],

        'second_input' => [
            'label' => 'Segundo',
        ],

    ],

    'file_upload' => [

        'actions' => [

            'download' => [
                'label' => 'Descargar',
            ],

            'open' => [
                'label' => 'Abrir en nueva pestaña',
            ],

        ],

        'editor' => [

            'label' => 'Editor de imagen',

            'actions' => [

                'cancel' => [
                    'label' => 'Cancelar',
                ],

                'drag_crop' => [
                    'label' => 'Modo arrastrar "recortar"',
                ],

                'drag_move' => [
                    'label' => 'Modo arrastrar "mover"',
                ],

                'flip_horizontal' => [
                    'label' => 'Voltear imagen horizontalmente',
                ],

                'flip_vertical' => [
                    'label' => 'Voltear imagen verticalmente',
                ],

                'move_down' => [
                    'label' => 'Mover imagen abajo',
                ],

                'move_left' => [
                    'label' => 'Mover imagen a la izquierda',
                ],

                'move_right' => [
                    'label' => 'Mover imagen a la derecha',
                ],

                'move_up' => [
                    'label' => 'Mover imagen arriba',
                ],

                'reset' => [
                    'label' => 'Restablecer',
                ],

                'rotate_left' => [
                    'label' => 'Rotar imagen a la izquierda',
                ],

                'rotate_right' => [
                    'label' => 'Rotar imagen a la derecha',
                ],

                'set_aspect_ratio' => [
                    'label' => 'Establecer relación de aspecto a :ratio',
                ],

                'save' => [
                    'label' => 'Guardar',
                ],

                'zoom_100' => [
                    'label' => 'Zoom imagen al 100%',
                ],

                'zoom_in' => [
                    'label' => 'Acercar',
                ],

                'zoom_out' => [
                    'label' => 'Alejar',
                ],

            ],

            'fields' => [

                'height' => [
                    'label' => 'Altura',
                    'unit' => 'px',
                ],

                'rotation' => [
                    'label' => 'Rotación',
                    'unit' => 'deg',
                ],

                'width' => [
                    'label' => 'Ancho',
                    'unit' => 'px',
                ],

                'x_position' => [
                    'label' => 'X',
                    'unit' => 'px',
                ],

                'y_position' => [
                    'label' => 'Y',
                    'unit' => 'px',
                ],

            ],

            'aspect_ratios' => [

                'label' => 'Relaciones de aspecto',

                'no_fixed' => [
                    'label' => 'Libre',
                ],

            ],

            'svg' => [

                'messages' => [
                    'confirmation' => 'Editar archivos SVG no se recomienda ya que puede resultar en pérdida de calidad al escalar.\n ¿Estás seguro de que quieres continuar?',
                    'disabled' => 'Editar archivos SVG está deshabilitado ya que puede resultar en pérdida de calidad al escalar.',
                ],

            ],

        ],

    ],

    'key_value' => [

        'actions' => [

            'add' => [
                'label' => 'Agregar fila',
            ],

            'delete' => [
                'label' => 'Eliminar fila',
            ],

            'reorder' => [
                'label' => 'Reordenar fila',
            ],

        ],

        'columns' => [

            'actions' => [
                'label' => 'Acciones',
            ],

            'reorder' => [
                'label' => 'Reordenar',
            ],

        ],

        'fields' => [

            'key' => [
                'label' => 'Clave',
            ],

            'value' => [
                'label' => 'Valor',
            ],

        ],

    ],

    'markdown_editor' => [

        'file_attachments_accepted_file_types_message' => 'Los archivos subidos deben ser de tipo: :values.',

        'file_attachments_max_size_message' => 'Los archivos subidos no deben ser mayores a :max kilobytes.',

        'tools' => [
            'attach_files' => 'Adjuntar archivos',
            'blockquote' => 'Cita en bloque',
            'bold' => 'Negrita',
            'bullet_list' => 'Lista con viñetas',
            'code_block' => 'Bloque de código',
            'heading' => 'Encabezado',
            'italic' => 'Cursiva',
            'link' => 'Enlace',
            'ordered_list' => 'Lista numerada',
            'redo' => 'Rehacer',
            'strike' => 'Tachado',
            'table' => 'Tabla',
            'undo' => 'Deshacer',
        ],

    ],

    'modal_table_select' => [

        'actions' => [

            'select' => [

                'label' => 'Seleccionar',

                'actions' => [

                    'select' => [
                        'label' => 'Seleccionar',
                    ],

                ],

            ],

        ],

    ],

    'radio' => [

        'boolean' => [
            'true' => 'Sí',
            'false' => 'No',
        ],

    ],

    'repeater' => [

        'columns' => [

            'actions' => [
                'label' => 'Acciones',
            ],

            'reorder' => [
                'label' => 'Reordenar',
            ],

        ],

        'actions' => [

            'add' => [
                'label' => 'Agregar a :label',
            ],

            'add_between' => [
                'label' => 'Insertar entre',
            ],

            'delete' => [
                'label' => 'Eliminar',
            ],

            'clone' => [
                'label' => 'Clonar',
            ],

            'reorder' => [
                'label' => 'Mover',
            ],

            'move_down' => [
                'label' => 'Mover abajo',
            ],

            'move_up' => [
                'label' => 'Mover arriba',
            ],

            'collapse' => [
                'label' => 'Colapsar',
            ],

            'expand' => [
                'label' => 'Expandir',
            ],

            'collapse_all' => [
                'label' => 'Colapsar todo',
            ],

            'expand_all' => [
                'label' => 'Expandir todo',
            ],

        ],

    ],

    'rich_editor' => [

        'actions' => [

            'attach_files' => [

                'label' => 'Subir archivo',

                'modal' => [

                    'heading' => 'Subir archivo',

                    'form' => [

                        'file' => [

                            'label' => [
                                'new' => 'Archivo',
                                'existing' => 'Reemplazar archivo',
                            ],

                        ],

                        'alt' => [

                            'label' => [
                                'new' => 'Texto alternativo',
                                'existing' => 'Cambiar texto alternativo',
                            ],

                        ],

                    ],

                ],

            ],

            'custom_block' => [

                'modal' => [

                    'heading' => 'Bloque personalizado',

                    'actions' => [

                        'insert' => [
                            'label' => 'Insertar',
                        ],

                        'save' => [
                            'label' => 'Guardar',
                        ],

                    ],

                ],

            ],

            'grid' => [

                'label' => 'Cuadrícula',

                'modal' => [

                    'heading' => 'Cuadrícula',

                    'form' => [

                        'preset' => [

                            'label' => 'Preestablecido',

                            'placeholder' => 'Ninguno',

                            'options' => [
                                'two' => 'Dos',
                                'three' => 'Tres',
                                'four' => 'Cuatro',
                                'five' => 'Cinco',
                                'two_start_third' => 'Dos (Inicio Tercio)',
                                'two_end_third' => 'Dos (Fin Tercio)',
                                'two_start_fourth' => 'Dos (Inicio Cuarto)',
                                'two_end_fourth' => 'Dos (Fin Cuarto)',
                            ],
                        ],

                        'columns' => [
                            'label' => 'Columnas',
                        ],

                        'from_breakpoint' => [

                            'label' => 'Desde punto de quiebre',

                            'options' => [
                                'default' => 'Todos',
                                'sm' => 'Pequeño',
                                'md' => 'Mediano',
                                'lg' => 'Grande',
                                'xl' => 'Extra grande',
                                '2xl' => 'Doble extra grande',
                            ],

                        ],

                        'is_asymmetric' => [
                            'label' => 'Dos columnas asimétricas',
                        ],

                        'start_span' => [
                            'label' => 'Tramo inicial',
                        ],

                        'end_span' => [
                            'label' => 'Tramo final',
                        ],

                    ],

                ],

            ],

            'link' => [

                'label' => 'Enlace',

                'modal' => [

                    'heading' => 'Enlace',

                    'form' => [

                        'url' => [
                            'label' => 'URL',
                        ],

                        'should_open_in_new_tab' => [
                            'label' => 'Abrir en nueva pestaña',
                        ],

                    ],

                ],

            ],

            'text_color' => [

                'label' => 'Color de texto',

                'modal' => [

                    'heading' => 'Color de texto',

                    'form' => [

                        'color' => [
                            'label' => 'Color',

                            'options' => [
                                'slate' => 'Pizarra',
                                'gray' => 'Gris',
                                'zinc' => 'Cinc',
                                'neutral' => 'Neutro',
                                'stone' => 'Piedra',
                                'mauve' => 'Malva',
                                'olive' => 'Oliva',
                                'mist' => 'Niebla',
                                'taupe' => 'Topo',
                                'red' => 'Rojo',
                                'orange' => 'Naranja',
                                'amber' => 'Ámbar',
                                'yellow' => 'Amarillo',
                                'lime' => 'Lima',
                                'green' => 'Verde',
                                'emerald' => 'Esmeralda',
                                'teal' => 'Verde azulado',
                                'cyan' => 'Cian',
                                'sky' => 'Cielo',
                                'blue' => 'Azul',
                                'indigo' => 'Índigo',
                                'violet' => 'Violeta',
                                'purple' => 'Púrpura',
                                'fuchsia' => 'Fucsia',
                                'pink' => 'Rosa',
                                'rose' => 'Rosa oscuro',
                            ],
                        ],

                        'custom_color' => [
                            'label' => 'Color personalizado',
                        ],

                    ],

                ],

            ],

        ],

        'file_attachments_accepted_file_types_message' => 'Los archivos subidos deben ser de tipo: :values.',

        'file_attachments_max_size_message' => 'Los archivos subidos no deben ser mayores a :max kilobytes.',

        'no_merge_tag_search_results_message' => 'No hay resultados de etiquetas de combinación.',

        'mentions' => [
            'no_options_message' => 'No hay opciones disponibles.',
            'no_search_results_message' => 'No hay resultados que coincidan con tu búsqueda.',
            'search_prompt' => 'Comienza a escribir para buscar...',
            'searching_message' => 'Buscando...',
        ],

        'toolbar' => [
            'label' => 'Barra de herramientas del editor',
        ],

        'tools' => [
            'align_center' => 'Alinear al centro',
            'align_end' => 'Alinear al final',
            'align_justify' => 'Justificar',
            'align_start' => 'Alinear al inicio',
            'attach_files' => 'Adjuntar archivos',
            'blockquote' => 'Cita en bloque',
            'bold' => 'Negrita',
            'bullet_list' => 'Lista con viñetas',
            'clear_formatting' => 'Limpiar formato',
            'code' => 'Código',
            'code_block' => 'Bloque de código',
            'custom_blocks' => 'Bloques',
            'details' => 'Detalles',
            'h1' => 'Título',
            'h2' => 'Encabezado 2',
            'h3' => 'Encabezado 3',
            'h4' => 'Encabezado 4',
            'h5' => 'Encabezado 5',
            'h6' => 'Encabezado 6',
            'grid' => 'Cuadrícula',
            'grid_delete' => 'Eliminar cuadrícula',
            'highlight' => 'Resaltar',
            'horizontal_rule' => 'Línea horizontal',
            'italic' => 'Cursiva',
            'lead' => 'Texto principal',
            'link' => 'Enlace',
            'merge_tags' => 'Etiquetas de combinación',
            'ordered_list' => 'Lista numerada',
            'paragraph' => 'Párrafo',
            'redo' => 'Rehacer',
            'small' => 'Texto pequeño',
            'strike' => 'Tachado',
            'subscript' => 'Subíndice',
            'superscript' => 'Superíndice',
            'table' => 'Tabla',
            'table_delete' => 'Eliminar tabla',
            'table_add_column_before' => 'Agregar columna antes',
            'table_add_column_after' => 'Agregar columna después',
            'table_delete_column' => 'Eliminar columna',
            'table_add_row_before' => 'Agregar fila arriba',
            'table_add_row_after' => 'Agregar fila abajo',
            'table_delete_row' => 'Eliminar fila',
            'table_merge_cells' => 'Combinar celdas',
            'table_split_cell' => 'Dividir celda',
            'table_toggle_header_row' => 'Alternar fila de encabezado',
            'table_toggle_header_cell' => 'Alternar celda de encabezado',
            'text_color' => 'Color de texto',
            'underline' => 'Subrayado',
            'undo' => 'Deshacer',
        ],

        'uploading_file_message' => 'Subiendo archivo...',

    ],

    'select' => [

        'actions' => [

            'clear' => [
                'label' => 'Limpiar selección',
            ],

            'create_option' => [

                'label' => 'Crear',

                'modal' => [

                    'heading' => 'Crear',

                    'actions' => [

                        'create' => [
                            'label' => 'Crear',
                        ],

                        'create_another' => [
                            'label' => 'Crear y crear otro',
                        ],

                    ],

                ],

            ],

            'edit_option' => [

                'label' => 'Editar',

                'modal' => [

                    'heading' => 'Editar',

                    'actions' => [

                        'save' => [
                            'label' => 'Guardar',
                        ],

                    ],

                ], ,

            ],

            'remove_option' => [
                'label' => 'Eliminar :label',
            ],

        ],

        'boolean' => [
            'true' => 'Sí',
            'false' => 'No',
        ],

        'loading_message' => 'Cargando...',

        'max_items_message' => 'Solo se pueden seleccionar :count.',

        'no_options_message' => 'No hay opciones disponibles.',

        'no_search_results_message' => 'No hay opciones que coincidan con tu búsqueda.',

        'placeholder' => 'Seleccionar una opción',

        'searching_message' => 'Buscando...',

        'search_label' => 'Buscar',

        'search_prompt' => 'Comienza a escribir para buscar...',

    ],

    'tags_input' => [

        'actions' => [

            'delete' => [
                'label' => 'Eliminar',
            ],

        ],

        'placeholder' => 'Nueva etiqueta',

        'tag_added' => 'Agregado: :tag',

        'tag_removed' => 'Eliminado: :tag',

    ],

    'text_input' => [

        'actions' => [

            'copy' => [
                'label' => 'Copiar',
                'message' => 'Copiado',
            ],

            'hide_password' => [
                'label' => 'Ocultar contraseña',
            ],

            'show_password' => [
                'label' => 'Mostrar contraseña',
            ],

        ],

    ],

    'toggle_buttons' => [

        'boolean' => [
            'true' => 'Sí',
            'false' => 'No',
        ],

    ],

];
