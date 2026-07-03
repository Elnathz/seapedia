<script setup lang="ts">
import type { HTMLAttributes, Ref } from "vue"
import { defaultDocument, useEventListener, useMediaQuery, useVModel } from "@vueuse/core"
import { TooltipProvider } from "reka-ui"
import { computed, ref } from "vue"
import { cn } from "@/lib/utils"
import { provideSidebarContext, SIDEBAR_COOKIE_MAX_AGE, SIDEBAR_COOKIE_NAME, SIDEBAR_KEYBOARD_SHORTCUT, SIDEBAR_WIDTH, SIDEBAR_WIDTH_ICON } from "./utils"

const props = withDefaults(defineProps<{
  defaultOpen?: boolean
  open?: boolean
  class?: HTMLAttributes["class"]
}>(), {
  defaultOpen: !defaultDocument?.cookie.includes(`${SIDEBAR_COOKIE_NAME}=false`),
  open: undefined,
})

const emits = defineEmits<{
  "update:open": [open: boolean]
}>()

const isMobile = useMediaQuery("(max-width: 768px)")
const openMobile = ref(false)

const open = useVModel(props, "open", emits, {
  defaultValue: props.defaultOpen ?? false,
  passive: (props.open === undefined) as false,
}) as Ref<boolean>

function setOpen(value: boolean) {
  open.value = value // emits('update:open', value)

  // This sets the cookie to keep the sidebar state.
  document.cookie = `${SIDEBAR_COOKIE_NAME}=${open.value}; path=/; max-age=${SIDEBAR_COOKIE_MAX_AGE}`
}

function setOpenMobile(value: boolean) {
  openMobile.value = value
}

// Helper to toggle the sidebar.
function toggleSidebar() {
  return isMobile.value ? setOpenMobile(!openMobile.value) : setOpen(!open.value)
}

useEventListener("keydown", (event: KeyboardEvent) => {
  if (event.key === SIDEBAR_KEYBOARD_SHORTCUT && (event.metaKey || event.ctrlKey)) {
    event.preventDefault()
    toggleSidebar()
  }
})

// Edge swipe (E1): dragging in from the left screen edge opens the mobile
// sidebar, mirroring the native drawer gesture. Desktop is untouched — the
// Sheet already handles swipe/overlay-tap to close.
const SWIPE_EDGE_ZONE = 24
const SWIPE_OPEN_THRESHOLD = 60
let swipeTracking = false
let swipeStartX = 0
let swipeStartY = 0

useEventListener("touchstart", (event: TouchEvent) => {
  if (!isMobile.value || openMobile.value || event.touches.length !== 1) {
    swipeTracking = false
    return
  }

  const touch = event.touches[0]
  swipeTracking = touch.clientX <= SWIPE_EDGE_ZONE
  swipeStartX = touch.clientX
  swipeStartY = touch.clientY
}, { passive: true })

useEventListener("touchend", (event: TouchEvent) => {
  if (!swipeTracking) {
    return
  }

  swipeTracking = false
  const touch = event.changedTouches[0]

  if (!touch) {
    return
  }

  const dx = touch.clientX - swipeStartX
  const dy = touch.clientY - swipeStartY

  // A mostly-horizontal drag past the threshold opens the drawer; the
  // dx >= |dy| check keeps a vertical scroll from triggering it.
  if (dx >= SWIPE_OPEN_THRESHOLD && dx >= Math.abs(dy)) {
    setOpenMobile(true)
  }
}, { passive: true })

// We add a state so that we can do data-state="expanded" or "collapsed".
// This makes it easier to style the sidebar with Tailwind classes.
const state = computed(() => open.value ? "expanded" : "collapsed")

provideSidebarContext({
  state,
  open,
  setOpen,
  isMobile,
  openMobile,
  setOpenMobile,
  toggleSidebar,
})
</script>

<template>
  <TooltipProvider :delay-duration="0">
    <div
      data-slot="sidebar-wrapper"
      :style="{
        '--sidebar-width': SIDEBAR_WIDTH,
        '--sidebar-width-icon': SIDEBAR_WIDTH_ICON,
      }"
      :class="cn('group/sidebar-wrapper has-data-[variant=inset]:bg-sidebar flex min-h-svh w-full', props.class)"
      v-bind="$attrs"
    >
      <slot />
    </div>
  </TooltipProvider>
</template>
