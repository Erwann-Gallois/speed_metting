# -*- mode: ruby -*-
# vi: set ft=ruby :

require_relative 'vagrant/config'

VAGRANTFILE_API_VERSION = "2"

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  $project_name = File.basename(File.dirname(__FILE__))

  $vm_name = "dev-" + $project_name.downcase

  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://vagrantcloud.com/search.
  config.vm.box = "generic/debian12"

  config.vm.hostname = $vm_name
  config.vm.define $vm_name

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # NOTE: This will enable public access to the opened port
  if ($HTTP_PORT_GUEST.nil? && $HTTP_PORT_HOST.nil?)
    config.vm.network "forwarded_port", guest: 8080, host: 8080
  else
    config.vm.network "forwarded_port", guest: $HTTP_PORT_GUEST, host: $HTTP_PORT_HOST
  end

  if ($WITH_MYSQL)
    if ($MYSQL_PORT_HOST.nil?)
      config.vm.network "forwarded_port", guest: 3306, host: 3306
    else
      config.vm.network "forwarded_port", guest: 3306, host: $MYSQL_PORT_HOST
    end
  end

  if ($WITH_POSTGRESQL)
    if ($POSTGRESQL_PORT_HOST.nil?)
      config.vm.network "forwarded_port", guest: 5432, host: 5432
    else
      config.vm.network "forwarded_port", guest: 5432, host: $POSTGRESQL_PORT_HOST
    end
  end

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine and only allow access
  # via 127.0.0.1 to disable public access
  # config.vm.network "$port", guest: 80, host: 8080, host_ip: "127.0.0.1"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  # config.vm.network "private_network", ip: "192.168.33.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder ".", "/home/vagrant/" + $project_name

  # Disable the default share of the current code directory. Doing this
  # provides improved isolation between the vagrant box and your host
  # by making sure your Vagrantfile isn't accessable to the vagrant box.
  # If you use this you may want to enable additional shared subfolders as
  # shown above.
  # config.vm.synced_folder ".", "/vagrant", disabled: true

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider "virtualbox" do |vb|
    vb.gui = false
    vb.name = $vm_name
    vb.cpus = $CPU_COUNT
    vb.memory = $MEMORY_SIZE
  end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Enable provisioning with a shell script. Additional provisioners such as
  # Ansible, Chef, Docker, Puppet and Salt are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", path: "vagrant/config-global.sh", env: {
      "TIMEZONE"                => $TIMEZONE,
      "PHP_VERSION"             => $PHP_VERSION.to_s,
      "PHP_MODULES"             => $PHP_MODULES,
      "PHP_WITH_OCI8"           => $PHP_WITH_OCI8.to_s,
      "WITH_MYSQL"              => $WITH_MYSQL.to_s,
      "MYSQL_DB_DATABASE"       => $MYSQL_DB_DATABASE,
      "MYSQL_DB_USERNAME"       => $MYSQL_DB_USERNAME,
      "MYSQL_DB_PASSWORD"       => $MYSQL_DB_PASSWORD,
      "WITH_POSTGRESQL"         => $WITH_POSTGRESQL.to_s,
      "POSTGRESQL_DB_DATABASE"  => $POSTGRESQL_DB_DATABASE,
      "POSTGRESQL_DB_USERNAME"  => $POSTGRESQL_DB_USERNAME,
      "POSTGRESQL_DB_PASSWORD"  => $POSTGRESQL_DB_PASSWORD
  }
end
