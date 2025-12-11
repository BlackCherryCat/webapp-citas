<template>
	<h1>Calendario</h1>
	<button @click="syncWithGoogle">🔄 Sincronizar con Google Calendar</button>
	<!-- FullCalendar -->
	<FullCalendar
		ref="calendar"
		:options="calendarOptions"
		@date-click="handleDateClick"
		@event-click="handleEventClick"
	/>
	<ModalEvento
		:isOpen="showEventModal"
		:date="modalDate"
		@close="handleModalClose"
	></ModalEvento>
	<ModalEventoEdit
		:isOpen="showEditModal"
		@close="handleModalClose"
		:appointmentData="modalAppointment"
	></ModalEventoEdit>
</template>

<script>
// Importa las dependencias necesarias
import FullCalendar from "@fullcalendar/vue3"
import dayGridPlugin from "@fullcalendar/daygrid"
import interactionPlugin from "@fullcalendar/interaction"
import ModalEvento from "@/components/ModalEvento.vue"
import ModalEventoEdit from "@/components/ModalEventoEdit.vue"
export default {
	components: {
		FullCalendar,
		ModalEvento,
		ModalEventoEdit,
	},
	data() {
		return {
			appointments: null,
			showEventModal: false,
			modalDate: null,
			modalAppointment: null,
			showEditModal: false,
			calendarOptions: {
				plugins: [dayGridPlugin, interactionPlugin],
				dayHeaders: false,
				dateClick: this.handleDateClick,
				eventClick: this.handleEventClick,
				initialView: "dayGridMonth",
				validRange: function (nowDate) {
					return {
						start: nowDate,
					}
				},
				height: "auto",
				events: [],
			},
		}
	},
	methods: {
		// Función para manejar la selección de fecha (al hacer clic en un día)
		handleDateClick(arg) {
			this.modalDate = arg.dateStr
			this.showEventModal = true
		},

		// Función para manejar clic en un evento
		handleEventClick(arg) {
			const appointment = this.appointments.find(
				(a) => a.id.toString() === arg.event.id,
			)
			if (appointment) {
				this.modalAppointment = appointment
				this.showEditModal = true
			}
		},
		handleModalClose() {
			this.showEditModal = false
			this.showEventModal = false
			this.modalDate = null
			this.modalAppointment = null
		},
		loadAppointmentsToCalendar() {
			if (this.appointments) {
				this.calendarOptions.events = this.appointments.map(
					(appointment) => ({
						id: appointment.id,
						title: appointment.title,
						start: appointment.date.split(" ")[0],
					}),
				)
			}
		},
		async syncWithGoogle() {
			try {
				const response = await this.$api.syncGoogleCalendar()
				if (response.url) {
					window.location.href = response.url // Redirige a la autorización de Google
				} else {
					alert("Sincronización completada.")
				}
			} catch (err) {
				console.error("Error al sincronizar:", err)
				alert("Error al sincronizar con Google Calendar.")
			}
		},
	},
	async mounted() {
		this.appointments = window.appointments
		this.loadAppointmentsToCalendar()
	},
}
</script>

<style scoped>
button {
	margin: 10px;
	padding: 10px;
	color: white;
	cursor: pointer;
}

button:disabled {
	background-color: #cccccc;
	cursor: not-allowed;
}

#calendar {
	margin-top: 20px;
}
</style>
