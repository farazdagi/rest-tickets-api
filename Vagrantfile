# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

    # API Server
    config.vm.define "api" do |api|
        api.vm.box = "precise64"
        api.vm.box_url = "http://files.vagrantup.com/precise64.box"
        api.vm.hostname = "api"
        api.vm.network :private_network, ip: "10.3.0.30"
        api.vm.synced_folder "./", "/project", type: "nfs"
    end

    # Provision with Ansible
    config.vm.provision :ansible do |ansible|
        ansible.playbook = "devops/site.apiservers.yml"
        ansible.inventory_path = "devops/hosts"
        #ansible.verbose = "v"
    end

    # Provider(s) configuration
    config.vm.provider "virtualbox" do |v|
      v.memory = 1024
    end
end
