
#
#    Copyright (C) 2014 Faye Sebastien <m@sfaye.com>
#    
#    This program is free software; you can redistribute it and/or
#    modify it under the terms of the GNU Lesser General Public
#    License as published by the Free Software Foundation; either
#    version 2.1 of the License, or (at your option) any later version.
#    
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
#    Lesser General Public License for more details.
#    
#    You should have received a copy of the GNU Lesser General Public
#    License along with this program; if not, write to the Free Software
#    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
#


# launch.py
# SUMO map analysis : setting sensors, creating graph files, etc.


####################################
#
#          Init
#
####################################


import os, subprocess, sys, socket, time, struct, random, argparse, re, math
sys.path.append(os.path.join(os.environ.get("SUMO_HOME", os.path.join(os.path.dirname(__file__), '..', '..', '..')), 'tools'))
import traci
import itertools
import shutil
import matplotlib as mpl
mpl.use('Agg')
import matplotlib.pyplot as plt
import networkx as nx

# debug message
def db(t=""):
    print "-- " + t









# SUMO/TRACI with-python method
if not traci.isEmbedded():
    parser = argparse.ArgumentParser(prog='launch', usage='%(prog)s [options]')
    parser.add_argument('-i', '--identifiant', nargs='?', help='ID', default="0")
    parser.add_argument('-m', '--modif', nargs='?', help='Enable network modifications', default="0")
    parser.add_argument('-p', '--propagation', nargs='?', help='Propagation distance', default=50)
    parser.add_argument('-n', '--nbsensors', nargs='?', help='Where', default=1)
    parser.add_argument('-l', '--wlanes', nargs='?', help='All junction lanes or traffic light lanes', default=1)
    args = parser.parse_args()
    IDENTIFIANT = str(args.identifiant)
    MODIF = str(args.modif)
    PROPAGATION = int(args.propagation)
    NBSENSORS = int(args.nbsensors)
    WLANES = int(args.wlanes)

    #
    os.putenv("LTO_IDENTIFIANT", str(IDENTIFIANT));
    os.putenv("LTO_MODIF", str(MODIF));
    os.putenv("LTO_PROPAGATION", str(PROPAGATION));
    os.putenv("LTO_NBSENSORS", str(NBSENSORS));
    os.putenv("LTO_WLANES", str(WLANES));
else:
    db("Creating the config file...");
    db("Launching sumo...")
    IDENTIFIANT = str(os.environ.get("LTO_IDENTIFIANT"));
    MODIF = str(os.environ.get("LTO_MODIF"));
    PROPAGATION = int(os.environ.get("LTO_PROPAGATION"));
    NBSENSORS = int(os.environ.get("LTO_NBSENSORS"));
    WLANES = int(os.environ.get("LTO_WLANES"));

# paths
SUMO_HOME = str(os.environ.get("SUMO_HOME"));
ABSPATH = str(os.getcwd())
CONFIG = ABSPATH + "/db/nets/" + IDENTIFIANT + ".sumo.cfg"
OUTPUT = ABSPATH + "/db/output/" + IDENTIFIANT + "-" + MODIF + "-" + str(WLANES) + "-" + str(NBSENSORS)
OUTPUT_MIN = ABSPATH + "/db/output/" + IDENTIFIANT
DATA = ABSPATH + "/db/data/" + IDENTIFIANT + "-" + MODIF + "-" + str(WLANES) + "-" + str(NBSENSORS)
DATA_MIN = ABSPATH + "/db/data/" + IDENTIFIANT
SENSORS_DATA = DATA + "-sensors.data"
SENSORS_DATA_NORMAL = ABSPATH + "/db/data/" + IDENTIFIANT + "-0-" + str(WLANES) + "-" + str(NBSENSORS) + "-sensors.data"
DET_SUMO_DATA = ABSPATH + "/db/nets/" + IDENTIFIANT + ".det.xml"
CSV_FILE = OUTPUT + "-" + str(PROPAGATION) + ".csv"
CSV_FILE_NORMAL = ABSPATH + "/db/output/" + IDENTIFIANT + "-0-" + str(WLANES) + "-" + str(NBSENSORS) + "-" + str(PROPAGATION) + ".csv"
LOCK1 = ABSPATH + "/db/locks/" + IDENTIFIANT + "-" + str(WLANES) + "-" + str(NBSENSORS) + ".lock"
LOCK2 = ABSPATH + "/db/locks/" + IDENTIFIANT + "-" + str(WLANES) + "-" + str(NBSENSORS) + "-" + str(PROPAGATION) + ".lock"
LOCK3 = ABSPATH + "/db/locks/" + IDENTIFIANT + "-" + MODIF + "-" + str(WLANES) + "-" + str(NBSENSORS) + "-" + str(PROPAGATION) + ".lock"







####################################
#
#          Propagation model
#
####################################


# If a propagation model is set, computes the path loss and an estimation of the propagation length
MODELE_PATHLOSS = -1
if PROPAGATION < 0:
    PT = 0 # Transmiter power dBm
    GTOT = 0 # Total gain dB
    R = -95 # Receiver sensitivity dBm
    PATHLOSS_MAX = PT + GTOT - R
    PATHLOSSA = 40.2 # Free space coefficient
    PATHLOSSB = 58.5 # 802.15.2-2003
    
    if PROPAGATION == -1: # Free space loss
        MODELE_PATHLOSS = 1
        PATHLOSS_MIN = PATHLOSSA + 20*math.log10(0.5)
        PROPAGATION = math.pow(10, ((PATHLOSS_MAX - 40.2)/20))
    elif PROPAGATION == -2: # IEEE 802.15.2-2003
        MODELE_PATHLOSS = 2
        PATHLOSS_MIN = PATHLOSSA + 20*math.log10(0.5)
        PROPAGATION = math.pow(10, ((PATHLOSS_MAX - PATHLOSSB)/33))*8







####################################
#
#          Functions
#
####################################


pos={}
sensorlist=[]
linklist = []

#
def tri(x,y):
    if x[1] > y[1]:
        return -1
    elif x[1]==y[1]:
        return 0
    else:
        return 1

#
def con_ex():
    global pos, sensorlist

    pos={}
    sensorlist=[]

    # Lock 2
    if os.path.isfile(LOCK2):
        os.remove(LOCK2)
    det_file = open(LOCK2, "a+")

    db("Computation of existing connections for each node...");

    f = open(SENSORS_DATA,'r')
    sensors = f.readlines()
    f.close()

    cpt = 0
    cpterr = 0
    cptcl = 0

    links = []
    for sensor in sensors:
        i=sensor.replace('\n', '').split(',',1)
        try:
            posx = float(i[0])
            posy = float(i[1])
            pos[cpt] = (posx,posy)
            sensorlist.append(cpt)
            links.append((cpt, posx))
            cpt+=1
        except IndexError:
            cpterr+=1
        except ValueError:
            cpterr+=1

    links.sort(cmp=tri)
    links_deux = {}
    dist_pre = -999999
    cptclu = 0
    links_deux[0] = []
    for sensor in links:
        if(abs(dist_pre - sensor[1]) > PROPAGATION):
            cptclu += 1
            links_deux[cptclu] = []
        links_deux[cptclu].append((sensor[0], pos[sensor[0]][1]))
        dist_pre = sensor[1]

    links = []
    decal = 0
    links_final = {}
    links_final[0] = []
    for cptclu in set(links_deux.keys()):
        links = links_deux[cptclu]
        links.sort(cmp=tri)
        dist_pre = -999999
        for sensor in links:
            if(abs(dist_pre - sensor[1]) > PROPAGATION):
                decal += 1
                links_final[decal] = []
            links_final[decal].append(sensor[0])
            dist_pre = sensor[1]

    links_deux = {}

    if os.path.isfile(CSV_FILE):
        os.remove(CSV_FILE)
    csv_file = open(CSV_FILE, "a+")

    links = []
    for clu in set(links_final.keys()):
        copielinks = list(links_final[clu])
        for sensorA in links_final[clu]:
            copielinks.remove(sensorA)
            for sensorB in copielinks:
                DISTANCE = math.sqrt(pow(pos[sensorA][0]-pos[sensorB][0],2) + pow(pos[sensorA][1]-pos[sensorB][1],2))
                if DISTANCE <= PROPAGATION:
                    WEI = 1.0
                    if MODELE_PATHLOSS > 0 and DISTANCE > 1:
                        if MODELE_PATHLOSS == 1:
                            WEI = (PATHLOSS_MAX - (PATHLOSSA + 20*math.log10(DISTANCE))) / (PATHLOSS_MAX - PATHLOSS_MIN)
                        if MODELE_PATHLOSS == 2:
                            if DISTANCE <= 8.0:
                                WEI = (PATHLOSS_MAX - (PATHLOSSA + 20*math.log10(DISTANCE))) / (PATHLOSS_MAX - PATHLOSS_MIN)
                            else:
                                WEI = (PATHLOSS_MAX - (PATHLOSSB + 33*math.log10(DISTANCE/8))) / (PATHLOSS_MAX - PATHLOSS_MIN)
                    if WEI > 0:
                        links.append(str(sensorA) + ';' + str(sensorB) + ';' + str(WEI))
    for sensorset in links:
        print >> csv_file, sensorset

    db("Saving datas...");

    # Unlock 2
    if os.path.isfile(LOCK2):
        os.remove(LOCK2)






# If necesary (MODIF != 0), we duplicate the data, to work directly on
if SENSORS_DATA_NORMAL != SENSORS_DATA and os.path.isfile(SENSORS_DATA_NORMAL):
    shutil.copy2(SENSORS_DATA_NORMAL, SENSORS_DATA)

if CSV_FILE_NORMAL != CSV_FILE and os.path.isfile(CSV_FILE_NORMAL):
    shutil.copy2(CSV_FILE_NORMAL, CSV_FILE)






####################################
#
#          Creating sensors
#
####################################


if not os.path.isfile(SENSORS_DATA):

    # Lock 1
    if os.path.isfile(LOCK1):
        os.remove(LOCK1)
    det_file = open(LOCK1, "a+")

    # SUMO configuration file
    if not traci.isEmbedded():

        confi = open(CONFIG, "w")
        """ --------------------------------- """
        print >> confi, """<?xml version="1.0" encoding="UTF-8"?>
<configuration xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://sumo.sf.net/xsd/sumoConfiguration.xsd">
    <input>
        <net-file value='""" + ABSPATH + """/db/nets/""" + IDENTIFIANT + """.net.xml'/>
    </input>
    <time>
        <begin value="0"/>
        <end value="4000"/>
    </time>
</configuration>"""
        """ --------------------------------- """
        confi.close()
        subprocess.call("%s/bin/sumo -c %s --python-script %s >> %s/logs/%s.log" % (SUMO_HOME, CONFIG, __file__, ABSPATH, IDENTIFIANT), shell=True, stdout=sys.stdout)

    else:
        db("Getting network dimensions...");
        # Real coords from SUMO
        netbounds1x = 0
        netbounds1y = 0
        netbounds2x = 0
        netbounds2y = 0
        cpt = 0
        for bound in traci.simulation.getNetBoundary():
            if cpt == 0:
                netbounds1x = bound[0]
                netbounds1y = bound[1]
            else:
                netbounds2x = bound[0]
                netbounds2y = bound[1]
            cpt+=1
    
    
        if os.path.isfile(OUTPUT_MIN + "-bound"):
            os.remove(OUTPUT_MIN + "-bound")
        stats = open(OUTPUT_MIN + "-bound", "a+")
        print >> stats, str((abs(netbounds2x-netbounds1x)*abs(netbounds2y-netbounds1y))/1000000)
        stats.close()
    
        db("Creating data files...");

        # Sensor coords
        if os.path.isfile(SENSORS_DATA):
            os.remove(SENSORS_DATA)
        det_file = open(SENSORS_DATA, "a+")
    
        nbdesensors = 0
    
        if NBSENSORS == 1:
            INTERSECTIONS = []
            if(WLANES == 0):
                INTERSECTIONS = traci.trafficlights.getIDList()
            else:
                INTERSECTIONS = traci.junction.getIDList()
    
            db("There are "+str(len(INTERSECTIONS))+" lanes")
            db("Creating the sensors...")
    
            f = open(DATA_MIN + "-junctions.data",'r')
            elements = list(f.readlines())
            f.close()
            liste_intersections = []
            for liste_intersection in elements:
                liste_intersections.append(liste_intersection.replace("\n", ""))
    
            for intersection in INTERSECTIONS:
                if intersection in liste_intersections:
                    try:
                        position = traci.junction.getPosition(intersection)
                        print >> det_file, str(position[0]) + ',' + str(position[1])
                        nbdesensors += 1
                    except traci.TraCIException:
                        err = 1
    
        else:
            LANES = []
            if(WLANES == 0):
                TLs = traci.trafficlights.getIDList()
                for TL in TLs:
                    LANES = LANES + traci.trafficlights.getControlledLanes(TL)
                LANES = list(set(LANES))
            else:
                LANES = traci.lane.getIDList()
    
            f = open(DATA_MIN + "-edges.data",'r')
            elements = list(f.readlines())
            f.close()
            liste_edges = []
            for liste_edge in elements:
                liste_edges.append(liste_edge.replace("\n", ""))
    
            db("Creating the sensors...")
    
            tabedgesdejafait = []
            for l in LANES:
                corEdge = traci.lane.getEdgeID(l)
                if ( (corEdge in liste_edges) and NBSENSORS != 4 ) or ( (corEdge in liste_edges) and (corEdge not in tabedgesdejafait) and NBSENSORS == 4 ):
    
                    # Complete coords of the lane + compute real coords
                    shape = traci.lane.getShape(l)
                    cpt = 0
                    px_begin = 0
                    py_begin = 0
                    px_end = 0
                    py_end = 0
                    for k in shape:
                        if cpt == 0:
                            px_begin = k[0] + netbounds1x
                            py_begin = (netbounds2y - netbounds1y) - (k[1] - netbounds1y)
                        else:
                            px_end = k[0] + netbounds1x
                            py_end = (netbounds2y - netbounds1y) - (k[1] - netbounds1y)
                        cpt+=1
    
                    lane_distance = traci.lane.getLength(l)
                    if lane_distance >= 50:

                        if NBSENSORS == 4:
                            tabedgesdejafait.append(corEdge)
    
                        if NBSENSORS == 2 or NBSENSORS == 3 or NBSENSORS == 4:
                            # sensor at the entrance of an intersection
                            position_begin = lane_distance
                            position_end = 0
                            px = (position_end*px_begin+position_begin*px_end)/lane_distance
                            py = (position_end*py_begin+position_begin*py_end)/lane_distance
    
                            print >> det_file, str(px) + ',' + str(py)
                            nbdesensors+=1
    
                        if NBSENSORS == 3:
                            # sensor at the exit of an intersection
                            position_begin = 0
                            position_end = lane_distance
                            px = (position_end*px_begin+position_begin*px_end)/lane_distance
                            py = (position_end*py_begin+position_begin*py_end)/lane_distance
    
                            print >> det_file, str(px) + ',' + str(py)
                            nbdesensors+=1
    
        db(str(nbdesensors) + " sensors were deployed on the map");
        sys.exit(1)


# Unlock 1
if os.path.isfile(LOCK1):
    os.remove(LOCK1)






#
if not os.path.isfile(CSV_FILE):
    con_ex()






####################################
#
#    If necesary (MODIF != 0), modification of the network
#
####################################

if int(MODIF) != 0:

    # Lock 3
    if os.path.isfile(LOCK3):
        os.remove(LOCK3)
    det_file = open(LOCK3, "a+")

    cpt = 0
    # Sensors and positions
    pos={}
    sensorlist=[]
    f = open(SENSORS_DATA,'r')
    sensors = f.readlines()
    f.close()
    cpt = 0
    cpterr = 0
    for sensor in sensors:
        i=sensor.replace('\n', '').split(',',1)
        try:
            pos[cpt] = (float(i[0]),float(i[1]))
            sensorlist.append(cpt)
            cpt+=1
        except IndexError:
            cpterr+=1
        except ValueError:
            cpterr+=1

    # Edges
    linklist = []
    f = open(CSV_FILE,'r')
    links = f.readlines()
    f.close()
    cpterr = 0
    for link in links:
        try:
            tmptab = link.split(';')
            linklist.append((int(tmptab[0]), int(tmptab[1]), float(tmptab[2])))
        except IndexError:
            cpterr+=1
        except ValueError:
            cpterr+=1
    links = []

    # NetworkX graph
    G=nx.Graph()
    for sensor in sensorlist:
        G.add_node(sensor)
    for edge in linklist:
        G.add_edge(edge[0], edge[1], weight=edge[2])

    # We remove articulation points
    if int(MODIF) == -1:
        for H in list(nx.connected_component_subgraphs(G)):
            for sensor in list(nx.articulation_points(H)):
                pos[sensor] = (-1,-1)

        if os.path.isfile(SENSORS_DATA):
            os.remove(SENSORS_DATA)

        det_file = open(SENSORS_DATA, "a+")
        for i in set(pos.keys()):
            posx = float(pos[i][0])
            posy = float(pos[i][1])
            if posx != -1 and posy != -1:
                print >> det_file, str(posx) + ',' + str(posy)
        det_file.close()



    # We add new nodes
    if int(MODIF) > 0:
        add_nodes = int(MODIF)
        COEF = 0.5
        DIAM = (add_nodes + 1) * (PROPAGATION * COEF)
        cpt = 0
        for sensor in sensorlist:
            cpt+=1

        samecomponent = {}
        poscomponent = {}
        cptco = 0
        for H in list(nx.connected_component_subgraphs(G)):
            samecomponent[cptco] = []
            cpts = 0
            avgx = 0
            avgy = 0
            for sensor in list(H.nodes()):
                samecomponent[cptco].append(sensor)
                avgx += pos[sensor][0]
                avgy += pos[sensor][1]
                cpts += 1
            poscomponent[cptco] = (float(avgx/cpts),float(avgy/cpts))
            maxdist = 0
            for sensor in list(H.nodes()):
               dist = math.sqrt(pow(pos[sensor][0]-poscomponent[cptco][0],2) + pow(pos[sensor][1]-poscomponent[cptco][1],2))
               if dist > maxdist:
                   maxdist = dist
            poscomponent[cptco] = (float(poscomponent[cptco][0]),float(poscomponent[cptco][1]),float(maxdist))
            cptco += 1
        det_file = open(SENSORS_DATA, "a+")
        dejafait = list(set(samecomponent.keys()))
        for cptcoA in set(samecomponent.keys()):
            dejafait.remove(cptcoA)
            for cptcoB in dejafait:
                if ((poscomponent[cptcoA][0] + poscomponent[cptcoA][2] + DIAM >= poscomponent[cptcoB][0] - poscomponent[cptcoB][2] - DIAM) or (poscomponent[cptcoA][0] - poscomponent[cptcoA][2] - DIAM <= poscomponent[cptcoB][0] + poscomponent[cptcoB][2] + DIAM)) and ((poscomponent[cptcoA][1] + poscomponent[cptcoA][2] + DIAM >= poscomponent[cptcoB][1] - poscomponent[cptcoB][2] - DIAM) or (poscomponent[cptcoA][1] - poscomponent[cptcoA][2] - DIAM <= poscomponent[cptcoB][1] + poscomponent[cptcoB][2] + DIAM)):
                    mindistance = -1
                    minsensorA = -1
                    minsensorB = -1
                    for sensorA in samecomponent[cptcoA]:
                        for sensorB in samecomponent[cptcoB]:
                            DISTANCE = math.sqrt(pow(pos[sensorA][0]-pos[sensorB][0],2) + pow(pos[sensorA][1]-pos[sensorB][1],2))
                            if mindistance == -1 or mindistance > DISTANCE:
                                mindistance = DISTANCE
                                minsensorA = sensorA
                                minsensorB = sensorB
                    if mindistance > -1 and mindistance <= DIAM:
                        noeudsnecessaires = int(math.ceil((mindistance/(PROPAGATION * COEF))-1))
                        if add_nodes >= noeudsnecessaires:
                            for di in range(noeudsnecessaires):
                                position_begin = (float(mindistance)/float(noeudsnecessaires + 1))*(float(di)+1.0)
                                position_end = mindistance - position_begin
                                px = (position_end*pos[minsensorA][0]+position_begin*pos[minsensorB][0])/mindistance
                                py = (position_end*pos[minsensorA][1]+position_begin*pos[minsensorB][1])/mindistance
                                pos[cpt] = (float(px),float(py))
                                sensorlist.append(cpt)
                                cpt += 1
                                print >> det_file, str(px) + ',' + str(py)

        det_file.close()

    con_ex()

    # Unlock 3
    if os.path.isfile(LOCK3):
        os.remove(LOCK3)






db("Done!")

if traci.isEmbedded():
    traci.close()