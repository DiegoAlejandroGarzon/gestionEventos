<?php

namespace App\Main;

class SideMenu
{
    /**
     * List of side menu items.
     */
    public static function menu(): array
    {
        $menu = [

            'calendario' => [
                'icon' => 'trello',
                'route_name' => 'event.calendar',
                'title' => 'Calendario'
            ],
            'event.findByDocument' => [
                'icon' => 'search',
                'route_name' => 'event.findByDocument',
                'title' => 'Verificar Entrada'
            ],
            'usuarios' => [
                'icon' => 'users',
                'title' => 'Users',
                'sub_menu' => [
                    'users.create' => [
                        'icon' => 'activity',
                        'route_name' => 'users.create',
                        'title' => 'Crear usuarios'
                    ],
                    'users.index' => [
                        'icon' => 'activity',
                        'route_name' => 'users.index',
                        'title' => 'Lista de Usuarios'
                    ]
                ]
            ],
            'Eventos' => [
                'icon' => 'file-text',
                'title' => 'Eventos',
                'sub_menu' => [
                    'event.create' => [
                        'icon' => 'file-text',
                        'route_name' => 'event.create',
                        'title' => 'Crear evento'
                    ],
                    'event.index' => [
                        'icon' => 'file-text',
                        'route_name' => 'event.index',
                        'title' => 'Lista de Eventos'
                    ]
                ]
            ],
            // 'Pagos' => [
            //     'icon' => 'file-text',
            //     'title' => 'Pagos',
            //     'sub_menu' => [
            //         'checkout.paypal' => [
            //             'icon' => 'file-text',
            //             'route_name' => 'checkout.paypal',
            //             'title' => 'Paypal'
            //         ]
            //     ]
            // ],
            'Configuraciones' => [
                'icon' => 'file-text',
                'title' => 'Configuraciones',
                'sub_menu' => [
                    'configuration.department' => [
                        'icon' => 'file-text',
                        'route_name' => 'department.index',
                        'title' => 'Departamentos'
                    ],
                    'configuration.city' => [
                        'icon' => 'file-text',
                        'route_name' => 'city.index',
                        'title' => 'ciudades'
                    ],
                    'configuration.ticketFeature' => [
                        'icon' => 'file-text',
                        'route_name' => 'ticketFeatures.index',
                        'title' => 'tickets'
                    ]
                ]
            ],
        ];
        // Verificar el entorno y agregar el menú de configuraciones solo si APP_ENV es 'local'
        if (env('APP_ENV') === 'local') {
            $menu['divider'] = "divider";
            $menu['dashboard'] = [
                'icon' => 'home',
                'title' => 'dashboard',
                'sub_menu' => [
                    'dashboard-overview-1' => [
                        'icon' => 'activity',
                        'route_name' => 'dashboard-overview-1',
                        'title' => 'Overview 1'
                    ],
                    'dashboard-overview-2' => [
                        'icon' => 'activity',
                        'route_name' => 'dashboard-overview-2',
                        'title' => 'Overview 2'
                    ],
                    'dashboard-overview-3' => [
                        'icon' => 'activity',
                        'route_name' => 'dashboard-overview-3',
                        'title' => 'Overview 3'
                    ],
                    'dashboard-overview-4' => [
                        'icon' => 'activity',
                        'route_name' => 'dashboard-overview-4',
                        'title' => 'Overview 4'
                    ]
                ]
            ];
            $menu['e-commerce'] = [
                'icon' => 'shopping-bag',
                'title' => 'E-Commerce',
                'sub_menu' => [
                    'categories' => [
                        'icon' => 'activity',
                        'route_name' => 'categories',
                        'title' => 'Categories'
                    ],
                    'add-product' => [
                        'icon' => 'activity',
                        'route_name' => 'add-product',
                        'title' => 'Add Product',
                    ],
                    'products' => [
                        'icon' => 'activity',
                        'title' => 'Products',
                        'sub_menu' => [
                            'product-list' => [
                                'icon' => 'zap',
                                'route_name' => 'product-list',
                                'title' => 'Product List'
                            ],
                            'product-grid' => [
                                'icon' => 'zap',
                                'route_name' => 'product-grid',
                                'title' => 'Product Grid'
                            ]
                        ]
                    ],
                    'transactions' => [
                        'icon' => 'activity',
                        'title' => 'Transactions',
                        'sub_menu' => [
                            'transaction-list' => [
                                'icon' => 'zap',
                                'route_name' => 'transaction-list',
                                'title' => 'Transaction List'
                            ],
                            'transaction-detail' => [
                                'icon' => 'zap',
                                'route_name' => 'transaction-detail',
                                'title' => 'Transaction Detail'
                            ]
                        ]
                    ],
                    'sellers' => [
                        'icon' => 'activity',
                        'title' => 'Sellers',
                        'sub_menu' => [
                            'seller-list' => [
                                'icon' => 'zap',
                                'route_name' => 'seller-list',
                                'title' => 'Seller List'
                            ],
                            'seller-detail' => [
                                'icon' => 'zap',
                                'route_name' => 'seller-detail',
                                'title' => 'Seller Detail'
                            ]
                        ]
                    ],
                    'reviews' => [
                        'icon' => 'activity',
                        'route_name' => 'reviews',
                        'title' => 'Reviews'
                    ],
                ]
            ];
            $menu['inbox'] = [
                'icon' => 'inbox',
                'route_name' => 'inbox',
                'title' => 'Inbox'
            ];
            $menu['file-manager'] = [
                'icon' => 'hard-drive',
                'route_name' => 'file-manager',
                'title' => 'File Manager'
            ];
            $menu['point-of-sale'] = [
                'icon' => 'credit-card',
                'route_name' => 'point-of-sale',
                'title' => 'Point of Sale'
            ];
            $menu['chat'] = [
                'icon' => 'message-square',
                'route_name' => 'chat',
                'title' => 'Chat'
            ];
            $menu['post'] = [
                'icon' => 'file-text',
                'route_name' => 'post',
                'title' => 'Post'
            ];
            $menu['calendar'] = [
                'icon' => 'calendar',
                'route_name' => 'calendar',
                'title' => 'Calendar'
            ];
            $menu['divider2'] = "divider";
            $menu['crud'] = [
                'icon' => 'edit',
                'title' => 'Crud',
                'sub_menu' => [
                    'crud-data-list' => [
                        'icon' => 'activity',
                        'route_name' => 'crud-data-list',
                        'title' => 'Data List'
                    ],
                    'crud-form' => [
                        'icon' => 'activity',
                        'route_name' => 'crud-form',
                        'title' => 'Form'
                    ]
                ]
            ];
            $menu['users'] = [
                'icon' => 'users',
                'title' => 'Users',
                'sub_menu' => [
                    'users-layout-1' => [
                        'icon' => 'activity',
                        'route_name' => 'users-layout-1',
                        'title' => 'Layout 1'
                    ],
                    'users-layout-2' => [
                        'icon' => 'activity',
                        'route_name' => 'users-layout-2',
                        'title' => 'Layout 2'
                    ],
                    'users-layout-3' => [
                        'icon' => 'activity',
                        'route_name' => 'users-layout-3',
                        'title' => 'Layout 3'
                    ]
                ]
            ];
            $menu['profile'] = [
                'icon' => 'trello',
                'title' => 'Profile',
                'sub_menu' => [
                    'profile-overview-1' => [
                        'icon' => 'activity',
                        'route_name' => 'profile-overview-1',
                        'title' => 'Overview 1'
                    ],
                    'profile-overview-2' => [
                        'icon' => 'activity',
                        'route_name' => 'profile-overview-2',
                        'title' => 'Overview 2'
                    ],
                    'profile-overview-3' => [
                        'icon' => 'activity',
                        'route_name' => 'profile-overview-3',
                        'title' => 'Overview 3'
                    ]
                ]
            ];
            $menu['pages'] = [
                'icon' => 'layout',
                'title' => 'Pages',
                'sub_menu' => [
                    'wizards' => [
                        'icon' => 'activity',
                        'title' => 'Wizards',
                        'sub_menu' => [
                            'wizard-layout-1' => [
                                'icon' => 'zap',
                                'route_name' => 'wizard-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'wizard-layout-2' => [
                                'icon' => 'zap',
                                'route_name' => 'wizard-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'wizard-layout-3' => [
                                'icon' => 'zap',
                                'route_name' => 'wizard-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'blog' => [
                        'icon' => 'activity',
                        'title' => 'Blog',
                        'sub_menu' => [
                            'blog-layout-1' => [
                                'icon' => 'zap',
                                'route_name' => 'blog-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'blog-layout-2' => [
                                'icon' => 'zap',
                                'route_name' => 'blog-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'blog-layout-3' => [
                                'icon' => 'zap',
                                'route_name' => 'blog-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'pricing' => [
                        'icon' => 'activity',
                        'title' => 'Pricing',
                        'sub_menu' => [
                            'pricing-layout-1' => [
                                'icon' => 'zap',
                                'route_name' => 'pricing-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'pricing-layout-2' => [
                                'icon' => 'zap',
                                'route_name' => 'pricing-layout-2',
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'invoice' => [
                        'icon' => 'activity',
                        'title' => 'Invoice',
                        'sub_menu' => [
                            'invoice-layout-1' => [
                                'icon' => 'zap',
                                'route_name' => 'invoice-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'invoice-layout-2' => [
                                'icon' => 'zap',
                                'route_name' => 'invoice-layout-2',
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'faq' => [
                        'icon' => 'activity',
                        'title' => 'FAQ',
                        'sub_menu' => [
                            'faq-layout-1' => [
                                'icon' => 'zap',
                                'route_name' => 'faq-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'faq-layout-2' => [
                                'icon' => 'zap',
                                'route_name' => 'faq-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'faq-layout-3' => [
                                'icon' => 'zap',
                                'route_name' => 'faq-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'login' => [
                        'icon' => 'activity',
                        'route_name' => 'login',
                        'title' => 'Login'
                    ],
                    'register' => [
                        'icon' => 'activity',
                        'route_name' => 'register',
                        'title' => 'Register'
                    ],
                    'error-page' => [
                        'icon' => 'activity',
                        'route_name' => 'error-page',
                        'title' => 'Error Page'
                    ],
                    'update-profile' => [
                        'icon' => 'activity',
                        'route_name' => 'update-profile',
                        'title' => 'Update profile'
                    ],
                    'change-password' => [
                        'icon' => 'activity',
                        'route_name' => 'change-password',
                        'title' => 'Change Password'
                    ]
                ]
            ];
            // $menu['divider'];
            $menu['components'] = [
                'icon' => 'inbox',
                'title' => 'Components',
                'sub_menu' => [
                    'grid' => [
                        'icon' => 'activity',
                        'title' => 'Grid',
                        'sub_menu' => [
                            'regular-table' => [
                                'icon' => 'zap',
                                'route_name' => 'regular-table',
                                'title' => 'Regular Table'
                            ],
                            'tabulator' => [
                                'icon' => 'zap',
                                'route_name' => 'tabulator',
                                'title' => 'Tabulator'
                            ]
                        ]
                    ],
                    'overlay' => [
                        'icon' => 'activity',
                        'title' => 'Overlay',
                        'sub_menu' => [
                            'modal' => [
                                'icon' => 'zap',
                                'route_name' => 'modal',
                                'title' => 'Modal'
                            ],
                            'slide-over' => [
                                'icon' => 'zap',
                                'route_name' => 'slide-over',
                                'title' => 'Slide Over'
                            ],
                            'notification' => [
                                'icon' => 'zap',
                                'route_name' => 'notification',
                                'title' => 'Notification'
                            ],
                        ]
                    ],
                    'tab' => [
                        'icon' => 'activity',
                        'route_name' => 'tab',
                        'title' => 'Tab'
                    ],
                    'accordion' => [
                        'icon' => 'activity',
                        'route_name' => 'accordion',
                        'title' => 'Accordion'
                    ],
                    'button' => [
                        'icon' => 'activity',
                        'route_name' => 'button',
                        'title' => 'Button'
                    ],
                    'alert' => [
                        'icon' => 'activity',
                        'route_name' => 'alert',
                        'title' => 'Alert'
                    ],
                    'progress-bar' => [
                        'icon' => 'activity',
                        'route_name' => 'progress-bar',
                        'title' => 'Progress Bar'
                    ],
                    'tooltip' => [
                        'icon' => 'activity',
                        'route_name' => 'tooltip',
                        'title' => 'Tooltip'
                    ],
                    'dropdown' => [
                        'icon' => 'activity',
                        'route_name' => 'dropdown',
                        'title' => 'Dropdown'
                    ],
                    'typography' => [
                        'icon' => 'activity',
                        'route_name' => 'typography',
                        'title' => 'Typography'
                    ],
                    'icon' => [
                        'icon' => 'activity',
                        'route_name' => 'icon',
                        'title' => 'Icon'
                    ],
                    'loading-icon' => [
                        'icon' => 'activity',
                        'route_name' => 'loading-icon',
                        'title' => 'Loading Icon'
                    ]
                ]
            ];
            $menu['forms'] = [
                'icon' => 'sidebar',
                'title' => 'Forms',
                'sub_menu' => [
                    'regular-form' => [
                        'icon' => 'activity',
                        'route_name' => 'regular-form',
                        'title' => 'Regular Form'
                    ],
                    'datepicker' => [
                        'icon' => 'activity',
                        'route_name' => 'datepicker',
                        'title' => 'Datepicker'
                    ],
                    'tom-select' => [
                        'icon' => 'activity',
                        'route_name' => 'tom-select',
                        'title' => 'Tom Select'
                    ],
                    'file-upload' => [
                        'icon' => 'activity',
                        'route_name' => 'file-upload',
                        'title' => 'File Upload'
                    ],
                    'wysiwyg-editor' => [
                        'icon' => 'activity',
                        'title' => 'Wysiwyg Editor',
                        'sub_menu' => [
                            'wysiwyg-editor-classic' => [
                                'icon' => 'zap',
                                'route_name' => 'wysiwyg-editor-classic',
                                'title' => 'Classic'
                            ],
                            'wysiwyg-editor-inline' => [
                                'icon' => 'zap',
                                'route_name' => 'wysiwyg-editor-inline',
                                'title' => 'Inline'
                            ],
                            'wysiwyg-editor-balloon' => [
                                'icon' => 'zap',
                                'route_name' => 'wysiwyg-editor-balloon',
                                'title' => 'Balloon'
                            ],
                            'wysiwyg-editor-balloon-block' => [
                                'icon' => 'zap',
                                'route_name' => 'wysiwyg-editor-balloon-block',
                                'title' => 'Balloon Block'
                            ],
                            'wysiwyg-editor-document' => [
                                'icon' => 'zap',
                                'route_name' => 'wysiwyg-editor-document',
                                'title' => 'Document'
                            ],
                        ]
                    ],
                    'validation' => [
                        'icon' => 'activity',
                        'route_name' => 'validation',
                        'title' => 'Validation'
                    ]
                ]
            ];
            $menu['widgets'] = [
                'icon' => 'hard-drive',
                'title' => 'Widgets',
                'sub_menu' => [
                    'chart' => [
                        'icon' => 'activity',
                        'route_name' => 'chart',
                        'title' => 'Chart'
                    ],
                    'slider' => [
                        'icon' => 'activity',
                        'route_name' => 'slider',
                        'title' => 'Slider'
                    ],
                    'image-zoom' => [
                        'icon' => 'activity',
                        'route_name' => 'image-zoom',
                        'title' => 'Image Zoom'
                    ]
                ]
            ];
            // 'logout' => [
            //     'icon' => 'activity',
            //     'route_name' => 'logout',
            //     'title' => 'Logout'
            // ],
        }

        return $menu;
    }
}
