<script lang="ts">
    let {
        class: className = '',
        alt = 'OptiBus',
        ...rest
    }: {
        class?: string;
        alt?: string;
        [key: string]: unknown;
    } = $props();
</script>

<img
    src="/branding/OptiBus-logo-mark.png"
    alt={alt}
    class={className}
    loading="eager"
    decoding="async"
    {...rest}
/>
