<?php

namespace Valourite\DynamicModels\Database\Seeders;

use Illuminate\Database\Seeder;
use Valourite\DynamicModels\Models\ModelType;

final class ModelTypeSeeder extends Seeder
{
    /**
     * This is purely for testing purposes
     * @return void
     */
    public function run(): void
    {
        $schema = '[{
                            "title": "text",
                            "Fields": [
                            {
                                "name": "text",
                                "type": "text",
                                "label": "Text",
                                "required": true,
                                "custom_id": "field-68b5a91c6707a",
                                "last_type": "text",
                                "maxlength": 10,
                                "helper_text": "helper",
                                "placeholder": "placeholder",
                                "prefix_icon": "academic-cap",
                                "prefix_text": "pre",
                                "suffix_icon": "adjustments-vertical",
                                "suffix_text": "sif",
                                "prefix_icon_color": null,
                                "suffix_icon_color": null
                            },
                            {
                                "name": "password",
                                "type": "password",
                                "label": "Password",
                                "required": true,
                                "custom_id": "field-68b5a945afe4e",
                                "last_type": "password",
                                "maxlength": 10,
                                "helper_text": "helper",
                                "placeholder": "placeholder",
                                "prefix_icon": "adjustments-horizontal",
                                "prefix_text": "pre",
                                "suffix_icon": "adjustments-vertical",
                                "suffix_text": "suf",
                                "prefix_icon_color": null,
                                "suffix_icon_color": null
                            },
                            {
                                "name": "email",
                                "type": "email",
                                "label": "Email",
                                "required": true,
                                "custom_id": "field-68b5a95ec97fd",
                                "last_type": "email",
                                "maxlength": 10,
                                "helper_text": "helper",
                                "placeholder": "placeholder",
                                "prefix_icon": "adjustments-horizontal",
                                "prefix_text": "pre",
                                "suffix_icon": "arrow-down-circle",
                                "suffix_text": "suf",
                                "prefix_icon_color": null,
                                "suffix_icon_color": null
                            },
                            {
                                "cols": 2,
                                "name": "textarea",
                                "rows": 4,
                                "type": "textarea",
                                "label": "Textarea",
                                "autosize": false,
                                "required": true,
                                "custom_id": "field-68b5a976ae2bc",
                                "last_type": "textarea",
                                "maxlength": 10,
                                "helper_text": "helper",
                                "placeholder": "placeholder"
                            }
                            ],
                            "custom_id": "section-68b5a91c67196",
                            "helper_text": null,
                            "column_count": null,
                            "is_collapsible": null,
                            "column_span_full": null
                        },
                        {
                            "title": "Numbers",
                            "Fields": [
                            {
                                "max": 20,
                                "min": 10,
                                "name": "number",
                                "step": 0.1,
                                "type": "number",
                                "label": "Number",
                                "required": true,
                                "custom_id": "field-68b5a990a850d",
                                "last_type": "number",
                                "helper_text": "helper",
                                "placeholder": "10",
                                "prefix_icon": "adjustments-horizontal",
                                "prefix_text": "pre",
                                "suffix_icon": "archive-box",
                                "suffix_text": "suf",
                                "prefix_icon_color": null,
                                "suffix_icon_color": null
                            },
                            {
                                "max": null,
                                "min": null,
                                "name": "number-no-options-set",
                                "step": null,
                                "type": "number",
                                "label": "Number-No-Options-Set",
                                "required": true,
                                "custom_id": "field-68b5a9b01c107",
                                "last_type": "number",
                                "helper_text": "helper",
                                "placeholder": "",
                                "prefix_icon": null,
                                "prefix_text": null,
                                "suffix_icon": null,
                                "suffix_text": null,
                                "prefix_icon_color": null,
                                "suffix_icon_color": null
                            }
                            ],
                            "custom_id": "section-68b5a990a86a8",
                            "helper_text": null,
                            "column_count": null,
                            "is_collapsible": null,
                            "column_span_full": null
                        },
                        {
                            "title": "Dates",
                            "Fields": [
                            {
                                "name": "date",
                                "type": "date",
                                "label": "Date",
                                "max_date": "2025-09-30",
                                "min_date": "2025-09-01",
                                "required": true,
                                "custom_id": "field-68b5a9ca544fe",
                                "last_type": "date",
                                "helper_text": "helper",
                                "prefix_icon": "academic-cap",
                                "suffix_icon": "adjustments-horizontal",
                                "display_format": "0",
                                "prefix_icon_color": null,
                                "suffix_icon_color": null
                            },
                            {
                                "name": "datetime",
                                "type": "datetime",
                                "label": "Datetime",
                                "max_date": "2025-09-30",
                                "min_date": "2025-09-01",
                                "required": true,
                                "custom_id": "field-68b5a9e1d9e89",
                                "last_type": "datetime",
                                "helper_text": "helper",
                                "prefix_icon": "adjustments-horizontal",
                                "suffix_icon": "arrow-down",
                                "display_format": "1",
                                "prefix_icon_color": null,
                                "suffix_icon_color": null
                            },
                            {
                                "name": "time",
                                "type": "time",
                                "label": "Time",
                                "required": true,
                                "custom_id": "field-68b5a9fa19ea0",
                                "last_type": "time",
                                "helper_text": "helper",
                                "prefix_icon": "adjustments-horizontal",
                                "suffix_icon": "archive-box-arrow-down",
                                "prefix_icon_color": null,
                                "suffix_icon_color": null
                            }
                            ],
                            "custom_id": "section-68b5a9ca5467c",
                            "helper_text": null,
                            "column_count": null,
                            "is_collapsible": null,
                            "column_span_full": null
                        },
                        {
                            "title": "Options",
                            "Fields": [
                            {
                                "name": "Select",
                                "type": "select",
                                "label": "Select",
                                "options": [
                                {
                                    "label": "label 1",
                                    "value": "Value 1"
                                },
                                {
                                    "label": "Label 2",
                                    "value": "Value 2"
                                }
                                ],
                                "required": true,
                                "custom_id": "field-68b5aa0836290",
                                "last_type": "select",
                                "helper_text": "helper",
                                "prefix_icon": "adjustments-horizontal",
                                "suffix_icon": "adjustments-vertical",
                                "prefix_icon_color": null,
                                "suffix_icon_color": null
                            },
                            {
                                "name": "Radio-inline",
                                "type": "radio",
                                "label": "Radio-Inline",
                                "inline": true,
                                "options": [
                                {
                                    "label": "label 1",
                                    "value": "value 1"
                                },
                                {
                                    "label": "label 2",
                                    "value": "value 2"
                                }
                                ],
                                "required": true,
                                "custom_id": "field-68b5aa268e3f3",
                                "last_type": "radio",
                                "helper_text": "helper"
                            },
                            {
                                "name": "radio-no-inline",
                                "type": "radio",
                                "label": "Radio-No-Inline",
                                "inline": false,
                                "options": [
                                {
                                    "label": "label 1",
                                    "value": "value 1"
                                },
                                {
                                    "label": "label 2",
                                    "value": "value 2"
                                }
                                ],
                                "required": true,
                                "custom_id": "field-68b5aa457527d",
                                "last_type": "radio",
                                "helper_text": "helper"
                            }
                            ],
                            "custom_id": "section-68b5aa0836364",
                            "helper_text": null,
                            "column_count": null,
                            "is_collapsible": null,
                            "column_span_full": null
                        }
                    ]';

        ModelType::create([
            ModelType::MODEL_TYPE_NAME            => 'Example Model',
            ModelType::MODEL_TYPE_DESCRIPTION     => 'Example Model Type to test everything',
            ModelType::MODEL_TYPE_SCHEMA          => json_decode($schema),
            ModelType::MODEL_TYPE_CONFIRMATION_MESSAGE => "The model using example model type has been created",
            ModelType::CAN_BE_CREATED               => true,
            ModelType::MODEL_TYPE_PARENT_MODEL => config('dynamic-models.parent_models')[0],
            ModelType::MODEL_TYPE_VERSION => 'v1.0.0'
        ]);
    }
}
