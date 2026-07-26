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
			type="target"
			:position="Position.Top"
			class="size-2 rounded-full" />

		<Handle
			:id="`${id}-spouse`"
			type="target"
			:position="Position.Left"
			class="size-2 rounded-full" />

		<div :id="id" class="border w-40 h-14 bg-pink-100 flex items-center justify-center">
			{{ data.label }}
		</div>

		<Handle
			type="source"
			:position="Position.Bottom"
			class="size-2 rounded-full" />
	</div>
</template>
