<template>
	<div class="view-container">
		<h1>Bienvenido a la aplicación</h1>

		<div v-if="appointments && appointments.length">
			<h3>Tus citas:</h3>

			<!-- Cada cita ahora tiene un contenedor tipo 'card' para diferenciarlas -->
			<div
				class="appointment-card"
				v-for="appointment in appointments"
				:key="appointment.id"
			>
				<!-- Contenido principal de la cita -->
				<div class="appointment-main">
					<div class="appointment-header">
						<strong class="appointment-title">{{
							appointment.title
						}}</strong>
						<span class="appointment-time">{{
							formatDate(appointment.date)
						}}</span>
					</div>

					<div
						v-if="user.id !== appointment.host_user_id"
						class="appointment-body"
					>
						<img :src="appointment.host_user.avatar" alt="Avatar" />
						<div class="appointment-meta">
							<p class="muted">
								La cita de {{ appointment.host_user.name }}
							</p>
							<p class="muted">
								Descripción: {{ appointment.description }}
							</p>
						</div>
					</div>

					<div v-else class="appointment-body">
						<img
							v-if="appointment.guest_user"
							:src="appointment.guest_user.avatar"
							alt="Avatar"
						/>
						<div class="appointment-meta">
							<p v-if="appointment.guest_user" class="muted">
								Su cita con {{ appointment.guest_user.name }}
							</p>
							<p v-else class="muted">
								Su cita con {{ appointment.guest_user_name }}
							</p>
							<p class="muted">
								Descripción: {{ appointment.description }}
							</p>
						</div>
					</div>
				</div>

				<!-- Acciones -->
				<div class="appointment-actions">
					<button @click="editAppointment(appointment)">
						Editar cita
					</button>
					<button
						class="secondary"
						@click="confirmDelete(appointment)"
					>
						Borrar cita
					</button>
				</div>
			</div>

			<ModalEventoEdit
				:isOpen="showEditModal"
				@close="handleModalClose"
				:appointmentData="modalAppointment"
			></ModalEventoEdit>
			<ModalEventoDelete
				:isOpen="showDeleteModal"
				@close="showDeleteModal = false"
				@delete="performDelete"
				:appointment="appointmentToDelete"
			/>
		</div>

		<h2 v-else>No tienes citas registradas</h2>
	</div>
</template>

<script>
import ModalEventoEdit from "@/components/ModalEventoEdit.vue"
import ModalEventoDelete from "@/components/ModalEventoDelete.vue"
import { format } from "date-fns"
import { es } from "date-fns/locale"

export default {
	components: {
		ModalEventoEdit,
		ModalEventoDelete
	},
	data() {
		return {
			appointments: null,
			user: null,
			showEditModal: false,
			modalAppointment: null,
			showDeleteModal: false,
			appointmentToDelete: null
		}
	},
	methods: {
		formatDate(dateString) {
			const date = new Date(dateString)
			return format(date, "'A las' HH:mm 'el' dd 'de' MMMM 'de' yyyy", {
				locale: es
			})
		},
		editAppointment(appointment) {
			this.showEditModal = true
			this.modalAppointment = appointment
		},
		confirmDelete(appointment) {
			this.appointmentToDelete = appointment
			this.showDeleteModal = true
		},
		async performDelete(appointment) {
			const result = await this.$api
				.deleteAppointment(appointment.id)
				.catch((error) => {
					console.error(error)
					return null
				})

			if (result) {
				window.appointments = window.appointments.filter(
					(a) => a.id !== appointment.id
				)
				this.appointments = this.appointments.filter(
					(a) => a.id !== appointment.id
				)
			}
			this.showDeleteModal = false
		},
		handleModalClose() {
			this.showEditModal = false
			this.modalAppointment = null
		}
	},
	async mounted() {
		this.appointments = window.appointments.sort(
			(a, b) => new Date(a.date) - new Date(b.date)
		)
		this.user = window.user
	}
}
</script>

<style scoped>
/* Card style para cada elemento de la lista de citas */
.appointment-card {
	display: flex;
	justify-content: space-between;
	align-items: center;
	width: 100%;
	margin: 12px 0;
	padding: 16px;
	background: var(--card-bg);
	color: var(--color-text);
	border-radius: 12px;
	box-shadow: var(--card-shadow);
	border: 1px solid rgba(0, 0, 0, 0.04);
	gap: 12px;
}

.muted {
	color: var(--color-text-mute);
}

.view-container h1,
h3 {
	color: var(--color-text);
}

/* Estructura interna */
.appointment-main {
	display: flex;
	flex-direction: column;
	flex: 1 1 auto;
	min-width: 0;
}

.appointment-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 12px;
	margin-bottom: 8px;
}

.appointment-title {
	font-weight: 600;
	font-size: 1.05rem;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.appointment-time {
	font-size: 0.95rem;
	color: var(--color-text-mute);
	white-space: nowrap;
}

/* Cuerpo con avatar y meta */
.appointment-body {
	display: flex;
	align-items: center;
	gap: 12px;
}

.appointment-meta p {
	margin: 0;
}

/* Acciones a la derecha */
.appointment-actions {
	display: flex;
	gap: 10px;
	flex-shrink: 0;
}

/* Avatar */
.appointment-card img {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	object-fit: cover;
}

/* Responsive: en pantallas pequeñas las tarjetas se apilan */
@media (max-width: 600px) {
	.appointment-card {
		flex-direction: column;
		align-items: flex-start;
	}
	.appointment-actions {
		margin-top: 10px;
		width: 100%;
		justify-content: flex-end;
	}
}

/* Mantener imágenes y estilos consistentes */
img {
	display: block;
}

/* Ajustes en modo oscuro (usa las variables definidas en main.css) */
:root.dark .appointment-card {
	background: var(--card-bg);
	box-shadow: var(--card-shadow);
	border-color: rgba(255, 255, 255, 0.04);
}
</style>
