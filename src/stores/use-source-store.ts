import { defineStore } from 'pinia'
import { SOURCE_TYPE_OPTIONS } from '@/constants.ts'
import type { Source } from '@/types'

export const useSourceStore = defineStore('sourceStore', () => {
	const getTypeLabel = (source: Source) => SOURCE_TYPE_OPTIONS.find((option) => option.value === source.sourceType)?.label
		?? source.sourceType
		?? null

	const getMetaLine = (source: Source) => [source.author, source.publication, source.repository]
		.filter(Boolean)
		.join(' · ')

	return {
		getTypeLabel,
		getMetaLine,
	}
})
