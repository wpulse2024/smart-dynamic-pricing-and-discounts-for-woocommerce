<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="size" :height="size" viewBox="0 0 20 20" fill="none">
        <path d="M9.99956 10.8785L13.7121 7.16602L14.7726 8.22652L9.99956 12.9995L5.22656 8.22652L6.28706 7.16602L9.99956 10.8785Z" fill="#525866"/>
    </svg>
</template>

<script>
export default {
    name: "DownIcon",
    props: {
        size: {
            type: String,
            default: "20",
        },
    },
}
</script>