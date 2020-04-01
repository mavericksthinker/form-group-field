<template>
    <div >
        <div class="flex items-center px-view border-b border-40">
            <div class="my-8">
                <label class="w-1/5 px-6 py-6 text-70 pt-2 leading-tight">
                    {{ this.field.heading }}
                </label>
            </div>
            <button v-if="showAddButton"
                    class="btn btn-default btn-primary ml-auto"
                    data-testid="run-add-button"
                    dusk="add-button"
                    @click.prevent="addChildren"
            >
            </button>
        </div>
        <template v-if="field.children.length > 0">
            <!-- ACTUAL FIELDS -->
            <group-fields v-for="(subfield, index) in field.children"
                               :key="`${field.attribute}-${index}`"
                               :resource="resource"
                               :resource-id="subfield[field.ID]"
                               :resource-name="resourceName"
                               :singular-name="field.singularLabel"
                               :field="field"
                               :child="subfield"
                               mode="form"
                               :via-resource="viaResource"
                               :via-resource-id="viaResourceId"
                               :via-relationship="viaRelationship"
                               :validation-errors="validationErrors"
            />
            <!-- ACTUAL FIELDS -->
        </template>
    </div>

</template>

<script>
    import { mapProps, Errors, FormField, HandlesValidationErrors } from "laravel-nova"

    import GroupFields from "./GroupFields"

    export default {
        mixins: [ FormField, HandlesValidationErrors ],

        props: {
            mode: {
                type: String,
                default: 'form',
                validator: val => ['modal', 'form'].includes(val),
            },
            ...mapProps([
                'resourceName',
                'resourceId',
                'viaResource',
                'viaResourceId',
                'viaRelationship',
            ]),
            field:{
                type: Array
            }
        },

        data: () => ({
            showAddButton: true,
            validationErrors: new Errors(),
        }),

        components: { GroupFields },

        async created(){
            this.field.children = []
        },

        methods: {
            /**
             * This adds a resource to the children
             */
            addChildren() {
               this.field.children.push(this.replaceIndexesInSchema(this.field));
               this.addButton();
            },
            /**
             * Overrides the fill method.
             */
            fill(formData) {
                this.field.children.forEach(child => child.fill(formData))
            },
            /**
             * This replaces the "{{index}}" values of the schema to
             * their actual index.
             *
             */
            replaceIndexesInSchema(field) {

                const schema = JSON.parse(JSON.stringify(field.schema));

                schema.fields.forEach(field => {
                    if (field.schema) {
                        field.schema.opened = false;
                        field.schema = this.replaceIndexesInSchema(field)
                    }

                    if (field.attribute) {
                        field.attribute = field.attribute.replace(
                            this.field.INDEX,
                            this.field.children.length
                        )

                    }
                });

                schema.heading = schema.heading.replace(
                    this.field.INDEX,
                    this.field.children.length + 1
                );

                schema.attribute = schema.attribute.replace(
                    this.field.INDEX,
                    this.field.children.length
                );

                return schema
            },
            addButton() {
                this.showAddButton = this.field.max ? this.field.children.length < this.field.max : true;
            }
        },
        computed: {
            shownViaNewRelationModal() {
                return this.mode === 'modal'
            }
        }
    }

</script>
