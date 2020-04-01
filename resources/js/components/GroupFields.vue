<template>
        <div>
            <button class="btn btn-default btn-danger float-right m-4 border-b border-40"
                    data-testid="run-add-button"
                    dusk="delete-button"
                    @click.prevent="showDeleteModal"
            >
            </button>
                <delete-modal v-if="deleteModal"

                              :resource-singular-name="this.singularName"
                              @close="closeDeleteModal"
                              @submit="removeChild"
                >

                </delete-modal>
            <!-- ACTUAL FIELDS -->
            <component v-for="subfield in child.fields"
                       :key="subfield.attribute"
                       :is="`${mode}-${subfield.component}`"
                       :errors="validationErrors"
                       :resource-id="resourceId"
                       :resource-name="resourceName"
                       :field="subfield"
                       :via-resource="viaResource"
                       :via-resource-id="viaResourceId"
                       :via-relationship="viaRelationship"
                       :shown-via-new-relation-modal="shownViaNewRelationModal"
            />
            <!-- ACTUAL FIELDS -->
        </div>

</template>

<script>
    import DeleteModal from '../Modals/Delete'

    export default {

        components: { DeleteModal },

        props: {
            shownViaNewRelationModal: {
                type: Boolean,
                default: false,
            },
            name:{
                type: String,
                default: 'Grouped Fields'
            },
            field: {
                type: Object,
                required: true
            },
            child: {
                type: Object,
                required: true
            },
            validationErrors: {
                type: Object,
                required: true,
            },
            resource: {
                type: Object
            },
            mode: {
                type: String,
                default: 'form',
            },
            resourceName: {
                type: String,
                required: true,
            },

            resourceId: {
                type: [Number, String],
            },

            viaResource: {
                type: String,
            },

            viaResourceId: {
                type: [Number, String],
            },

            viaRelationship: {
                type: String,
            },
            singularName: {
                type: String,
                required : true
            },
        },

        data :() => ({
            deleteModal : false
        }),

        async created() {
            this.child.fill = this.fill
        },

        methods: {
            /**
             * Fill the formData with the children.
             */
            fill(formData) {
                this.child.fields.forEach(field => field.fill(formData));
                if (this.child[this.field.ID]) {
                    formData.append(`${this.field.attribute}[${this.index}][${this.field.ID}]`, this.child[this.field.ID])
                }
            },

            /**
             * Get the component depending on the field.
             */
            getComponent(field) {
                if (['belongs-to-field', 'file-field'].includes(field.component)) {
                    return 'nested-' + field.component
                }
                return field.component
            },
            removeChild(){
                this.field.children.splice(this.field.children.indexOf(this.child), 1);
                this.deleteModal = false;
            },
            showDeleteModal(){
               this.deleteModal = true;
            },
            closeDeleteModal() {
                this.deleteModal = false;
            }
        }
    }
</script>
