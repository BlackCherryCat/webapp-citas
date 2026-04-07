<template>
	<div class="view-container">
		<h1>Calendario</h1>

		<div class="calendar-actions">
			<button @click="syncWithGoogle">
				🔄 Sincronizar con Google Calendar
			</button>
		</div>

		<!-- FullCalendar dentro de un wrapper para poder controlar estilos y padding -->
		<div id="calendar" class="calendar-wrapper">
			<FullCalendar
				ref="calendar"
				:options="calendarOptions"
				@date-click="handleDateClick"
				@event-click="handleEventClick"
			/>
		</div>

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
	</div>
</template>

<script>
// Importa las dependencias necesarias
import FullCalendar from "@fullcalendar/vue3"
import dayGridPlugin from "@fullcalendar/daygrid"
import interactionPlugin from "@fullcalendar/interaction"
import esLocale from "@fullcalendar/core/locales/es"
import ModalEvento from "@/components/ModalEvento.vue"
import ModalEventoEdit from "@/components/ModalEventoEdit.vue"

export default {
	components: {
		FullCalendar,
		ModalEvento,
		ModalEventoEdit
	},
	data() {
		return {
			appointments: null,
			showEventModal: false,
			modalDate: null,
			modalAppointment: null,
			showEditModal: false,
			_themeObserver: null,
			// calendarOptions defined here with safe handlers; some handlers will be set in mounted()
			calendarOptions: {
				plugins: [dayGridPlugin, interactionPlugin],
				dayHeaders: false,
				// dateClick and eventClick will be assigned in mounted to ensure `this` is available
				dateClick: null,
				eventClick: null,
				locale: esLocale,
				initialView: "dayGridMonth",
				validRange: function (nowDate) {
					return {
						start: nowDate
					}
				},
				height: "auto",
				events: [],
				// Apply inline event styling at render time using CSS variables.
				// This ensures events pick up colors from :root / :root.dark variables.
				eventDidMount: function (info) {
					try {
						const root = document.documentElement
						const styles = getComputedStyle(root)
						const bg = (
							styles.getPropertyValue("--color-primary") ||
							"#2a7b9b"
						).trim()
						const text = (
							styles.getPropertyValue(
								"--color-primary-contrast"
							) || "#ffffff"
						).trim()
						info.el.style.background = bg
						info.el.style.borderColor = bg
						info.el.style.color = text
						info.el.style.borderRadius = "6px"
						info.el.style.padding = "4px 6px"
					} catch (e) {
						// ignore if CSS variables are not accessible
					}
				}
			}
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
			const appointment =
				this.appointments &&
				this.appointments.find((a) => a.id.toString() === arg.event.id)
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
						start: appointment.date.split(" ")[0]
					})
				)
				// If calendar already mounted, refresh events
				this._safeRerenderEvents()
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

		_safeRerenderEvents() {
			// Helper para evitar errores si la instancia no existe aún
			try {
				const cal =
					this.$refs.calendar &&
					this.$refs.calendar.getApi &&
					this.$refs.calendar.getApi()
				if (cal && typeof cal.refetchEvents === "function") {
					cal.refetchEvents()
				} else if (cal && typeof cal.rerenderEvents === "function") {
					cal.rerenderEvents()
				} else if (cal && typeof cal.render === "function") {
					cal.render()
				}
			} catch (e) {
				// ignore
			}
		},

		updateCalendarTheme() {
			// Read CSS vars from root and update calendar options / events
			try {
				const root = document.documentElement
				const styles = getComputedStyle(root)
				const eventBg = (
					styles.getPropertyValue("--color-primary") || "#2a7b9b"
				).trim()
				const eventText = (
					styles.getPropertyValue("--color-primary-contrast") ||
					"#ffffff"
				).trim()
				this.calendarOptions.eventColor = eventBg
				this.calendarOptions.eventTextColor = eventText
				// Try to apply styles to already-rendered event elements as well
				this._safeApplyInlineEventStyles(eventBg, eventText)
				this._safeRerenderEvents()
			} catch (e) {
				// ignore
			}
		},

		_safeApplyInlineEventStyles(bg, text) {
			try {
				const rootEl = this.$refs.calendar && this.$refs.calendar.$el
				// Fallback: query DOM for event elements and set inline style
				const container = rootEl || document
				const nodes =
					container.querySelectorAll &&
					container.querySelectorAll(".fc-daygrid-event")
				if (nodes && nodes.length) {
					nodes.forEach((el) => {
						el.style.background = bg
						el.style.borderColor = bg
						el.style.color = text
						el.style.borderRadius = "6px"
						el.style.padding = "4px 6px"
					})
				}
			} catch (e) {
				// ignore
			}
		}
	},
	async mounted() {
		// Assign handlers that refer to `this`
		this.calendarOptions.dateClick = this.handleDateClick
		this.calendarOptions.eventClick = this.handleEventClick

		this.appointments = window.appointments
		this.loadAppointmentsToCalendar()

		// Initialize calendar theme based on current CSS variables
		this.updateCalendarTheme()

		// Observe changes to the <html> class attribute so we can update theme dynamically
		try {
			const root = document.documentElement
			this._themeObserver = new MutationObserver((mutations) => {
				for (const m of mutations) {
					if (m.attributeName === "class") {
						this.updateCalendarTheme()
					}
				}
			})
			this._themeObserver.observe(root, {
				attributes: true,
				attributeFilter: ["class"]
			})
		} catch (e) {
			// ignore
		}
	},
	beforeUnmount() {
		if (this._themeObserver) {
			this._themeObserver.disconnect()
			this._themeObserver = null
		}
	}
}
</script>

<style>
/* Contenedor principal de la vista para mantener legibilidad */
.view-container {
	max-width: var(--content-max-width);
	margin: 0 auto;
	padding: 16px;
}
.view-container h1 {
	color: var(--color-text);
}

/* Tarjeta que envuelve el calendario */
.calendar-card {
	background: var(--card-bg);
	color: var(--color-text);
	border-radius: 12px;
	padding: 16px;
	box-shadow: var(--card-shadow);
	border: 1px solid rgba(0, 0, 0, 0.04);
}

/* Acciones encima del calendario */
.calendar-actions {
	display: flex;
	justify-content: flex-end;
	margin-bottom: 12px;
}

/* Botones: apariencia coherente con variables globales */
.fc button,
.calendar-actions button {
	margin: 0;
	padding: 8px 12px;
	color: #fff;
	background: var(--color-primary);
	border: none;
	border-radius: 8px;
	cursor: pointer;
}

/* Disabled state */
.fc button:disabled {
	background-color: #cccccc;
	cursor: not-allowed;
}

/* Wrapper del calendario para control de espaciado */
.calendar-wrapper {
	margin-top: 8px;
}

/* Ajustes visuales de FullCalendar dentro del wrapper */
.calendar-wrapper .fc {
	background: transparent;
	color: var(--color-text);
}

/* Asegura que los eventos tengan algo de padding y bordes suaves */
.fc .fc-daygrid-event {
	border-radius: 6px;
	padding: 4px 6px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

/* Hover y foco más suaves */
.fc .fc-daygrid-event:hover {
	filter: brightness(0.96);
	cursor: pointer;
}

/* Cursor pointer SOLO para días activos (no .fc-day-disabled ni other-month) */
.fc
	.fc-daygrid-day:not(.fc-day-disabled):not(.fc-daygrid-day-other):not(
		.fc-day-other
	),
.fc
	.fc-daygrid-day:not(.fc-day-disabled):not(.fc-daygrid-day-other):not(
		.fc-day-other
	)
	.fc-daygrid-day-frame,
.fc
	.fc-daygrid-day:not(.fc-day-disabled):not(.fc-daygrid-day-other):not(
		.fc-day-other
	)
	.fc-daygrid-day-top,
.fc
	.fc-daygrid-day:not(.fc-day-disabled):not(.fc-daygrid-day-other):not(
		.fc-day-other
	)
	.fc-daygrid-day-number {
	cursor: pointer !important;
}

/* Asegurar que los días deshabilitados se muestren con cursor por defecto */
.fc .fc-day-disabled,
.fc .fc-daygrid-day.fc-daygrid-day-other,
.fc .fc-day-other {
	cursor: default !important;
}

/* Opcional: efecto visual al pasar el ratón en días activos */
.fc .fc-daygrid-day:not(.fc-day-disabled):not(.fc-daygrid-day-other):hover {
	background-color: var(--color-primary-light) !important;
}

/* Responsive: en pantallas pequeñas reducir padding */
@media (max-width: 600px) {
	.view-container {
		padding: 12px;
	}
	.calendar-card {
		padding: 12px;
	}
	button {
		padding: 8px 10px;
	}
}
</style>
