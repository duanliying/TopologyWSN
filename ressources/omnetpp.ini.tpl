[General]
network = WSNRouting
sim-time-limit = ${simDuration=2} min
ned-path = ../../src;..
tkenv-image-path = ../../images;

repeat = 5

**.coreDebug = false
**.debug = false
**.playgroundSizeX = {MAXX} m
**.playgroundSizeY = {MAXY} m
**.playgroundSizeZ = 100 m
**.numHosts = ${numHosts={NB_SENSORS}}

**.world.useTorus = false

**.connectionManager.sendDirect = false
**.connectionManager.pMax = 1.1mW
**.connectionManager.sat = -100dBm
**.connectionManager.alpha = 2.0
**.connectionManager.carrierFrequency = 2.4E+9Hz

**.node[*].nic.phy.usePropagationDelay = false
**.node[*].nic.phy.analogueModels = xmldoc("config.xml")
**.node[*].nic.phy.sensitivity = -100dBm
**.node[*].nic.phy.maxTXPower = 1.1mW
**.node[*].nic.phy.initialRadioState = 0
**.node[*].nic.phy.useThermalNoise = true
**.node[*].mobility.z = 100


{SENSORS}


**.node[*].nic.mac.txPower = ${txPower=0.1,1} mW

[Config convergecast]
description="Wireless sensor network with typical convergecast traffic towards one sink (host 0) and dynamic routing."
**.node[*].networkType = "WiseRoute"
**.node[*].netwl.stats = true
**.node[0].netwl.routeFloodsInterval = 1200 s
**.node[*].netwl.sinkAddress = 0
**.node[*].netwl.headerLength = 24 bit
**.node[0].appl.nbPackets = 0
**.node[*].appl.nbPackets = 10
**.node[*].appl.destAddr = 0
**.node[*].appl.trafficType = "periodic"
**.node[*].appl.trafficParam = 30 s  # each node sends 1 packet every 30 seconds 
**.node[*].appl.initializationTime = 30 s
**.node[*].appl.headerLength = 50 byte

[Config flooding]
description="Wireless sensor network with the sink flooding the network periodically."
**.node[*].networkType = "Flood"
**.node[*].netwl.stats = true
**.node[*].netwl.headerLength = 24 bit
**.node[0].appl.nbPackets = 10
**.node[*].appl.nbPackets = 0
**.node[*].appl.destAddr = -1
**.node[*].appl.broadcastPackets = true
**.node[*].appl.trafficType = "periodic"
**.node[*].appl.trafficParam = 30 s  # each node sends 1 packet every 30 seconds 
**.node[*].appl.initializationTime = 10 s
**.node[*].appl.headerLength = 50 byte

[Config probabilisticBcast]
description="Wireless sensor network using probabilistic broadcast."
extends=flooding
**.node[*].networkType = "AdaptiveProbabilisticBroadcast"

