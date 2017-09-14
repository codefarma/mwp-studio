<?php
return <<<'JSON'
{
    "tables": [
        {
            "name": "studio_file_catalog",
            "columns": {
                "file_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "file_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "file_file": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 1028,
                    "name": "file_file",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "file_type": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 15,
                    "name": "file_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "file_data": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 0,
                    "name": "file_data",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "file_last_analyzed": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "",
                    "length": 11,
                    "name": "file_last_analyzed",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "file_id"
                    ]
                },
                "file_last_analyzed": {
                    "type": "key",
                    "name": "file_last_analyzed",
                    "length": [
                        null
                    ],
                    "columns": [
                        "file_last_analyzed"
                    ]
                },
                "file_path": {
                    "type": "key",
                    "name": "file_path",
                    "length": [
                        767
                    ],
                    "columns": [
                        "file_file"
                    ]
                },
                "file_type": {
                    "type": "key",
                    "name": "file_type",
                    "length": [
                        null
                    ],
                    "columns": [
                        "file_type"
                    ]
                }
            }
        },
        {
            "name": "studio_class_catalog",
            "columns": {
                "class_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "class_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "class_name": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "class_name",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "class_extends": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "class_extends",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "class_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "class_data",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "class_file": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 1056,
                    "name": "class_file",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "class_line": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "",
                    "length": 6,
                    "name": "class_line",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "class_catalog_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "",
                    "length": 11,
                    "name": "class_catalog_time",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "class_id"
                    ]
                },
                "class_name": {
                    "type": "key",
                    "name": "class_name",
                    "length": [
                        null,
                        767
                    ],
                    "columns": [
                        "class_name",
                        "class_file"
                    ]
                },
                "class_catalog_time": {
                    "type": "key",
                    "name": "class_catalog_time",
                    "length": [
                        null
                    ],
                    "columns": [
                        "class_catalog_time"
                    ]
                }
            }
        },
        {
            "name": "studio_function_catalog",
            "columns": {
                "function_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "function_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "function_name": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "function_name",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "function_class": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "function_class",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "function_type": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 15,
                    "name": "function_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "function_args": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "",
                    "length": 2,
                    "name": "function_args",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "function_data": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 0,
                    "name": "function_data",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "function_file": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 1056,
                    "name": "function_file",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "function_line": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "",
                    "length": 6,
                    "name": "function_line",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "function_catalog_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "",
                    "length": 11,
                    "name": "function_catalog_time",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "function_id"
                    ]
                },
                "function_name": {
                    "type": "key",
                    "name": "function_name",
                    "length": [
                        null,
                        null,
                        null,
                        null
                    ],
                    "columns": [
                        "function_name",
                        "function_class",
                        "function_type",
                        "function_catalog_time"
                    ]
                },
                "function_file": {
                    "type": "key",
                    "name": "function_file",
                    "length": [
                        767
                    ],
                    "columns": [
                        "function_file"
                    ]
                }
            }
        },
        {
            "name": "studio_hook_catalog",
            "columns": {
                "hook_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "hook_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_name": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "hook_name",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_type": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 15,
                    "name": "hook_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_callback_name": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "hook_callback_name",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_callback_class": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "hook_callback_class",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_callback_type": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": null,
                    "length": 15,
                    "name": "hook_callback_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "hook_data",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_file": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 1056,
                    "name": "hook_file",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_line": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 6,
                    "name": "hook_line",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_args": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 2,
                    "name": "hook_args",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_priority": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 6,
                    "name": "hook_priority",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_catalog_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "",
                    "length": 11,
                    "name": "hook_catalog_time",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "hook_id"
                    ]
                },
                "hook_name": {
                    "type": "key",
                    "name": "hook_name",
                    "length": [
                        null,
                        null
                    ],
                    "columns": [
                        "hook_name",
                        "hook_type"
                    ]
                },
                "hook_file": {
                    "type": "key",
                    "name": "hook_file",
                    "length": [
                        767
                    ],
                    "columns": [
                        "hook_file"
                    ]
                },
                "hook_callback": {
                    "type": "key",
                    "name": "hook_callback",
                    "length": [
                        null,
                        null
                    ],
                    "columns": [
                        "hook_callback_name",
                        "hook_callback_type"
                    ]
                }
            }
        }
    ]
}
JSON;
