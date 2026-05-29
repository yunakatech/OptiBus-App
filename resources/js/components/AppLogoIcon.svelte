<script lang="ts">
    let {
        class: className = '',
        alt = 'Qbus',
        ...rest
    }: {
        class?: string;
        alt?: string;
        [key: string]: unknown;
    } = $props();
</script>

<img
    src="/branding/qbus-logo-mark.png"
    alt={alt}
    class={className}
    loading="eager"
    decoding="async"
    {...rest}
/>
