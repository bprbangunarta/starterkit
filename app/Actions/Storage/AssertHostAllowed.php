<?php

namespace App\Actions\Storage;

class AssertHostAllowed
{
    /**
     * Never reachable from the storage settings, not even by the allow-list: the metadata service of the cloud providers, and the NAT64
     * range, which can carry any IPv4 address (a private one included) in IPv6 form.
     */
    private const METADATA_PREFIXES = ['169.254.', 'fd00:ec2:', '64:ff9b:'];

    /**
     * Why a host typed on the screen may not be used, or null when it may. A host must resolve to public addresses only, unless it is
     * in `storage_connections.allowed_hosts` (an internal server the organization runs on purpose).
     */
    public function refusal(string $host): ?string
    {
        $host = strtolower(trim($host));
        $addresses = $this->addresses($host);

        if ($addresses === []) {
            return 'Alamat host tidak ditemukan.';
        }

        $listed = in_array($host, (array) config('storage_connections.allowed_hosts'), true);

        foreach ($addresses as $address) {
            if ($this->isMetadata($address)) {
                return 'Alamat layanan metadata tidak boleh dipakai.';
            }

            if (! $listed && ! $this->isPublic($address)) {
                return 'Alamat internal tidak diizinkan. Daftarkan host di STORAGE_ALLOWED_HOSTS bila server itu memang dipakai.';
            }
        }

        return null;
    }

    /** @return list<string> */
    protected function addresses(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return [$host];
        }

        $found = gethostbynamel($host);

        return $found === false ? [] : $found;
    }

    /** The address in its one shortest form, so leading zeros and capitals cannot hide it, and an IPv4 address inside IPv6 counts as the IPv4 address. */
    private function canonical(string $address): string
    {
        $packed = @inet_pton($address);

        if ($packed === false) {
            return strtolower($address);
        }

        $mapped = strlen($packed) === 16 && str_starts_with($packed, str_repeat("\0", 10)."\xff\xff");

        return (string) inet_ntop($mapped ? substr($packed, 12) : $packed);
    }

    private function isMetadata(string $address): bool
    {
        $canonical = $this->canonical($address);

        foreach (self::METADATA_PREFIXES as $prefix) {
            if (str_starts_with($canonical, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isPublic(string $address): bool
    {
        return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
