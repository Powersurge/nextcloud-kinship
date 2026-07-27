import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useEventStore = defineStore('eventStore', () => {
	const showFormModal = ref(false)
	const eventForm = ref({

	})

	const openEventForm = (formData: Record<any, any>) => {
		eventForm.value = formData
		showFormModal.value = true
	}

	return { showFormModal, openEventForm, eventForm }
})
