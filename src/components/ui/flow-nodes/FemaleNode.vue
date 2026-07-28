<script setup lang="ts">
import { Handle, Position } from '@vue-flow/core'

defineProps<{
	id: string,
	data: Record<any, any>
}>()
</script>

<template>
	<div>
		<Handle
			id="default"
			type="target"
			:position="Position.Top"
			class="size-2 rounded-full" />

		<Handle
			:id="`${id}-spouse-left`"
			type="target"
			:position="Position.Left"
			class="size-2 rounded-full" />

		<div :id="id" class="!border !border-gray-600 w-40 h-14 bg-pink-100 flex items-center justify-center text-center">
			{{ data.label }}
		</div>

		<Handle
			:id="`${id}-spouse-right`"
			type="source"
			:position="Position.Right"
			class="size-2 rounded-full" />

		<Handle
			id="default"
			type="source"
			:position="Position.Bottom"
			class="size-2 rounded-full" />
	</div>
</template>
