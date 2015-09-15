
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


# analysis.py
# Graph analysis


####################################
#
#          Init
#
####################################


import os, subprocess, sys, socket, time, struct, random, argparse, re, math
import matplotlib as mpl
mpl.use('Agg')
import matplotlib.pyplot as plt
import networkx as nx
import scipy.spatial

#
parser = argparse.ArgumentParser(prog='launch', usage='%(prog)s [options]')
parser.add_argument('-i', '--identifiant', nargs='?', help='ID', default="0")
parser.add_argument('-m', '--modif', nargs='?', help='Enable network modifications', default="0")
parser.add_argument('-p', '--propagation', nargs='?', help='Propagation distance', default=50)
parser.add_argument('-n', '--nbsensors', nargs='?', help='Where', default=1)
parser.add_argument('-l', '--wlanes', nargs='?', help='All junction lanes or traffic light lanes', default=1)
parser.add_argument('-t', '--analysistype', nargs='?', help='Type of analysis', default=1)
parser.add_argument('-s', '--paramsup', nargs='?', help='Additional parameter', default="")

#
args = parser.parse_args()
IDENTIFIANT = str(args.identifiant)
MODIF = str(args.modif)
PROPAGATION = int(args.propagation)
NBSENSORS = int(args.nbsensors)
WLANES = int(args.wlanes)
ANALYSIS_TYPE = int(args.analysistype)
PARAMSUP = str(args.paramsup)

# paths
ABSPATH = str(os.getcwd())
OUTPUT = ABSPATH + "/db/output/" + IDENTIFIANT + "-" + MODIF + "-" + str(WLANES) + "-" + str(NBSENSORS)
OUTPUT_COMPLET = OUTPUT + "-" + str(PROPAGATION)
OUTPUT_MIN = ABSPATH + "/db/output/" + IDENTIFIANT
DATA = ABSPATH + "/db/data/" + IDENTIFIANT + "-" + MODIF + "-"  + str(WLANES) + "-" + str(NBSENSORS)
SPLOTS = ABSPATH + "/db/splots/" + IDENTIFIANT + "-" + MODIF + "-" + str(WLANES) + "-" + str(NBSENSORS) + "-" + str(PROPAGATION) + ".data"
SENSORS_DATA = DATA + "-sensors.data"
CSV_FILE = OUTPUT + "-" + str(PROPAGATION) + ".csv"







####################################
#
#          Functions
#
####################################


pos={}
sensorlist=[]
linklist = []


# set Sensors and positions
def get_sensors():
    global pos, sensorlist
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

# set Edges
def get_links():
    global linklist
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


# return NetworkX Graph
def get_graph():
    global sensorlist, linklist
    G=nx.Graph()
    for sensor in sensorlist:
        G.add_node(sensor)
    for edge in linklist:
        G.add_edge(edge[0], edge[1], weight=edge[2])
    return G


# 
def print_analysis(path, unit):
    f = open(path,'r')
    value = list(f.readlines())
    value = float(value[0].replace("\n", ""))
    f.close()
    print str(value) + " " + str(unit)


# return the lagest component
def get_largest_component():
    global G
    FILEDATA = OUTPUT_COMPLET+"-largest_connected_component"
    if not os.path.isfile(FILEDATA):
        DISTANCE_MAX = 0
        VAL = 0
        nbval = 0
        for H in list(nx.connected_component_subgraphs(G)):
            dejafait = list(H.nodes())
            for sensor in list(H.nodes()):
                dejafait.remove(sensor)
                for sensorB in dejafait:
                    distance_tmp = math.sqrt(pow(pos[sensor][0]-pos[sensorB][0],2) + pow(pos[sensor][1]-pos[sensorB][1],2))
                    if distance_tmp > DISTANCE_MAX:
                        DISTANCE_MAX = distance_tmp
                        VAL = nbval
            nbval += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(VAL)
        filew.close()

    f = open(FILEDATA,'r')
    value = list(f.readlines())
    value = float(value[0].replace("\n", ""))
    f.close()
    return value








########################### Base








# Image of all sensors
if ANALYSIS_TYPE == 1:

    if not os.path.isfile(OUTPUT+"-sensors.png"):
        get_sensors()
        get_links()
        G = get_graph()

        maxd = 0.0
        listno = {}
        for sensor in sensorlist:
            deg = float(G.degree(sensor))
            try:
                listno[deg].append(sensor)
            except KeyError:
                listno[deg] = []
                listno[deg].append(sensor)
            if maxd < deg:
                maxd = deg

        G2=nx.house_graph()

        for degree in set(listno.keys()):
            senlist = []
            posgraph = {}
            for n in listno[degree]:
                senlist.append(n)
                posgraph[n] = (pos[n][0], pos[n][1])

            color = "#ff0000"
            #if(degree > maxd - 9*maxd/10):
            #    color = "#ff0000"

            nx.draw_networkx_nodes(G2,posgraph,nodelist=senlist,node_size=1,alpha=0.5,node_color=color,linewidths=0.0)

        plt.axis('off')
        plt.savefig(OUTPUT+"-sensors.png")
        #plt.savefig(OUTPUT+"-sensors.pdf")

    print "<img src='"+(OUTPUT.replace(ABSPATH, '.'))+"-sensors.png'/>"


# Image of max component sensors
if ANALYSIS_TYPE == 44:

    if not os.path.isfile(OUTPUT_COMPLET+"-mcc_sensors.png"):
        get_sensors()
        get_links()
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        G2=nx.house_graph()
        senlist = []
        posgraph = {}
        for sensor in list(H.nodes()):
            senlist.append(sensor)
            posgraph[sensor] = (pos[sensor][0], pos[sensor][1])

        nx.draw_networkx_nodes(G2,posgraph,nodelist=senlist,node_size=1,alpha=1,node_color='#ff0000',linewidths=0.0)
        plt.axis('off')
        plt.savefig(OUTPUT_COMPLET +"-mcc_sensors.png")
        #plt.savefig(OUTPUT_COMPLET+"-mcc_sensors.pdf")

    print "<img src='"+(OUTPUT_COMPLET.replace(ABSPATH, '.'))+"-mcc_sensors.png'/>"


# Total sensors
if ANALYSIS_TYPE == 2:

    get_sensors()

    FILEDATA = OUTPUT_COMPLET+"-nodes"
    if not os.path.isfile(FILEDATA):
        nbsen = 0
        for sensor in sensorlist:
            nbsen += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbsen)
        filew.close()

    print_analysis(FILEDATA, "nodes")


# Sensors per square kilometer
if ANALYSIS_TYPE == 3:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-nodes_bound"
    if not os.path.isfile(FILEDATA):

        f = open(OUTPUT_MIN+"-bound",'r')
        bound = list(f.readlines())
        bound = float(bound[0].replace("\n", ""))
        f.close()

        if not os.path.isfile(OUTPUT_COMPLET+"-nodes"):
            nbsen = 0
            for sensor in sensorlist:
                nbsen += 1

            filew = open(OUTPUT_COMPLET+"-nodes", "a+")
            print >> filew, str(nbsen)
            filew.close()

        f = open(OUTPUT_COMPLET+"-nodes",'r')
        nodes = list(f.readlines())
        nodes = float(nodes[0].replace("\n", ""))
        f.close()

        filew = open(FILEDATA, "a+")
        print >> filew, str(nodes/bound)
        filew.close()

    print_analysis(FILEDATA, "nodes per square kilometer")


# Network dimension
if ANALYSIS_TYPE == 4:

    print_analysis(OUTPUT_MIN+"-bound", "square kilometers")










########################### Connected components










# Image of cliques / connected components
if ANALYSIS_TYPE == 5 or ANALYSIS_TYPE == 8 or ANALYSIS_TYPE == 11:

    if not os.path.isfile(OUTPUT_COMPLET+"-"+str(ANALYSIS_TYPE)+str(PARAMSUP)+"-view.png"):
        get_sensors()
        get_links()
        G = get_graph()
        G2=nx.house_graph()

        posgraph={}
        cpt=0
        listtosee = []
        if ANALYSIS_TYPE == 5:
            listtosee = list(nx.find_cliques(G))
        elif ANALYSIS_TYPE == 8:
            listtosee = list(nx.k_clique_communities(G,int(PARAMSUP)))
        elif ANALYSIS_TYPE == 11:
            listtosee = list(nx.connected_components(G))

        maxfactor = 0.0
        minfactor = 1.0
        for sensorset in list(listtosee):
            if len(sensorset) > maxfactor:
                maxfactor = float(len(sensorset))
        #    if len(sensorset) < 10:
        #        listtosee.remove(sensorset)

        listtosee.reverse()

        r_begin = 0
        g_begin = 0
        b_begin = 255
        r_end = 255
        g_end = 0
        b_end = 0

        for sensorset in listtosee:
            sensorlist2=[]
            sensorlist=[]
            posgraph2={}
            cpt_avg = 0.0
            cpt_avgx = 0.0
            cpt_avgy = 0.0
            DISTANCE_MAX = 0.0
            dejafait = list(sensorset)
            for sensor in sensorset:
                sensorlist2.append(sensor)
                posgraph2[sensor] = (pos[sensor][0], pos[sensor][1])
                cpt_avgx += pos[sensor][0]
                cpt_avgy += pos[sensor][1]
                cpt_avg += 1.0
                dejafait.remove(sensor)
                for sensorB in dejafait:
                    distance_tmp = math.sqrt(pow(pos[sensor][0]-pos[sensorB][0],2) + pow(pos[sensor][1]-pos[sensorB][1],2))
                    if distance_tmp > DISTANCE_MAX:
                        DISTANCE_MAX = distance_tmp
            posgraph[cpt] = (cpt_avgx/cpt_avg,cpt_avgy/cpt_avg)
            sensorlist.append(cpt)

            nb_sensors = float(len(sensorlist2))
            localfactor = nb_sensors

            rgbR = max(0, min(255, int(r_begin + ((r_end - r_begin)/maxfactor)*localfactor)))
            rgbG = max(0, min(255, int(g_begin + ((g_end - g_begin)/maxfactor)*localfactor)))
            rgbB = max(0, min(255, int(b_begin + ((b_end - b_begin)/maxfactor)*localfactor)))
            color = '#' + str(format((rgbR<<16 | rgbG<<8 | rgbB), '06x'))
            alphav = max(0.0, min(1.0, 0.3 + ((0.4/maxfactor)*localfactor)))
            size = DISTANCE_MAX

            nx.draw_networkx_nodes(G2,posgraph,nodelist=sensorlist,node_size=size,alpha=alphav,node_color=color,linewidths=0.0)

            #nx.draw_networkx_nodes(G2,posgraph2,nodelist=sensorlist2,node_size=1.0,alpha=alphav,node_color=color,linewidths=0.0)
            cpt+=1

        plt.axis('off')
        plt.savefig(OUTPUT_COMPLET+"-"+str(ANALYSIS_TYPE)+str(PARAMSUP)+"-view.png")
        #plt.savefig(OUTPUT_COMPLET+"-"+str(ANALYSIS_TYPE)+str(PARAMSUP)+"-view.pdf")

    print "<img src='"+(OUTPUT_COMPLET.replace(ABSPATH, '.'))+"-"+str(ANALYSIS_TYPE)+str(PARAMSUP)+"-view.png'/>"


# 3D image of connected components
if ANALYSIS_TYPE == 300:

    if not os.path.isfile(SPLOTS):
        get_sensors()
        get_links()
        G = get_graph()

        listtosee = list(nx.connected_components(G))
        listtosee.reverse()

        posgraph={}
        res = {}
        xs = []
        ys = []
        cpt=0
        for sensorset in listtosee:
            cpt_avg = 0.0
            cpt_avgx = 0.0
            cpt_avgy = 0.0
            for sensor in sensorset:
                cpt_avgx += pos[sensor][0]
                cpt_avgy += pos[sensor][1]
                cpt_avg += 1.0
            posgraph[cpt] = (cpt_avgx/cpt_avg,cpt_avgy/cpt_avg)
            nb_sensors = float(len(sensorset))
            try:
                res[int(posgraph[cpt][0])][int(posgraph[cpt][1])] = nb_sensors
            except KeyError:
                res[int(posgraph[cpt][0])] = {}
                res[int(posgraph[cpt][0])][int(posgraph[cpt][1])] = nb_sensors
            xs.append(int(posgraph[cpt][0]))
            ys.append(int(posgraph[cpt][1]))
            cpt+=1

        xs.sort()
        ys.sort()

        filew = open(SPLOTS, "a+")
        for x in xs:
            for y in ys:
                try:
                    print >> filew, str(x) + " " + str(y) + " " + str(res[x][y]) + "\n"
                except KeyError:
                    print >> filew, str(x) + " " + str(y) + " 0.0\n"
            #print >> filew, "\n"
        filew.close()



# Number of cliques
if ANALYSIS_TYPE == 6:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cliques"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbcli = 0
        for sensorset in list(nx.find_cliques(G)):
            nbcli += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbcli)
        filew.close()

    print_analysis(FILEDATA, "cliques")


# Numer of nodes in the largest clique
if ANALYSIS_TYPE == 7:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-max_clique"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        maxc = 0.0
        filew = open(FILEDATA, "a+")
        for sensorset in list(nx.find_cliques(G)):
            if maxc < len(sensorset):
                maxc = len(sensorset)
        print >> filew, maxc
       
        filew.close()

    print_analysis(FILEDATA, "nodes")


# Number of k-cliques
if ANALYSIS_TYPE == 9:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-"+PARAMSUP+"-cliques"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbcli = 0
        for sensorset in list(nx.k_clique_communities(G,int(PARAMSUP))):
            nbcli += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbcli)
        filew.close()

    print_analysis(FILEDATA, PARAMSUP + "-cliques")


# Number of connected components
if ANALYSIS_TYPE == 12:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-connected_components"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        print >> filew, str(nx.number_connected_components(G))
        filew.close()

    print_analysis(FILEDATA, "components")


# Average connected component diameter (hops)
if ANALYSIS_TYPE == 13:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-connected_components_diameter"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbc = 0
        nba = 0
        for H in list(nx.connected_component_subgraphs(G)):
            nbc += 1.0
            length=nx.shortest_path_length(H)

            maxl = 0
            for a in set(length.keys()):
                for b in set(length[a].keys()):
                    if(length[a][b] > maxl):
                        maxl = length[a][b]
            nba += float(maxl)

        filew = open(FILEDATA, "a+")
        print >> filew, str(nba/nbc)
        filew.close()

    print_analysis(FILEDATA, "")


# Average connected component diameter (meters)
if ANALYSIS_TYPE == 14:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-connected_components_diameterm"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        nbc = 0
        nba = 0
        for H in list(nx.connected_component_subgraphs(G)):
            DISTANCE_MAX = 0
            dejafait = list(H.nodes())
            for sensor in list(H.nodes()):
                dejafait.remove(sensor)
                for sensorB in dejafait:
                    distance_tmp = math.sqrt(pow(pos[sensor][0]-pos[sensorB][0],2) + pow(pos[sensor][1]-pos[sensorB][1],2))
                    if distance_tmp > DISTANCE_MAX:
                        DISTANCE_MAX = distance_tmp
            nbc += 1.0
            nba += float(DISTANCE_MAX)

        filew = open(FILEDATA, "a+")
        print >> filew, str(nba/nbc)
        filew.close()

    print_analysis(FILEDATA, " meters")



# Number of biconnected components
if ANALYSIS_TYPE == 35:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-biconnected_components"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbc = 0
        for H in list(nx.biconnected_component_subgraphs(G)):
            nbc += 1.0

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbc)
        filew.close()

    print_analysis(FILEDATA, "components")


# Connected component per square kilometer
if ANALYSIS_TYPE == 38:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-connected_components_bound"
    if not os.path.isfile(FILEDATA):

        f = open(OUTPUT_MIN+"-bound",'r')
        bound = list(f.readlines())
        bound = float(bound[0].replace("\n", ""))
        f.close()

        if not os.path.isfile(OUTPUT_COMPLET+"-connected_components"):
            G = get_graph()
            filew = open(OUTPUT_COMPLET+"-connected_components", "a+")
            print >> filew, str(nx.number_connected_components(G))
            filew.close()

        f = open(OUTPUT_COMPLET+"-connected_components",'r')
        cc = list(f.readlines())
        cc = float(cc[0].replace("\n", ""))
        f.close()

        filew = open(FILEDATA, "a+")
        print >> filew, str(cc/bound)
        filew.close()

    print_analysis(FILEDATA, "components per square kilometer")



# Biconnected component per square kilometer
if ANALYSIS_TYPE == 39:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-biconnected_components_bound"
    if not os.path.isfile(FILEDATA):

        f = open(OUTPUT_MIN+"-bound",'r')
        bound = list(f.readlines())
        bound = float(bound[0].replace("\n", ""))
        f.close()

        if not os.path.isfile(OUTPUT_COMPLET+"-biconnected_components"):
            G = get_graph()
            nbc = 0
            for H in list(nx.biconnected_component_subgraphs(G)):
                nbc += 1.0
            filew = open(OUTPUT_COMPLET+"-biconnected_components", "a+")
            print >> filew, str(nbc)
            filew.close()

        f = open(OUTPUT_COMPLET+"-biconnected_components",'r')
        cc = list(f.readlines())
        cc = float(cc[0].replace("\n", ""))
        f.close()

        filew = open(FILEDATA, "a+")
        print >> filew, str(cc/bound)
        filew.close()

    print_analysis(FILEDATA, "components per square kilometer")


# Connected component / total sensors
if ANALYSIS_TYPE == 40:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-connected_components_p"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        nbb = (float(nx.number_connected_components(G))/float(len(list(G.nodes()))))
        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb)
        filew.close()

    print_analysis(FILEDATA, "components / total nodes")


# Biconnected component / total sensors
if ANALYSIS_TYPE == 41:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-biconnected_components_p"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        nbb = (float(len(list(nx.biconnected_component_subgraphs(G))))/float(len(list(G.nodes()))))
        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb)
        filew.close()

    print_analysis(FILEDATA, "components / total nodes")


# Connected components with one node
if ANALYSIS_TYPE == 42:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-1_connected_components"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        nbc = 0
        nbctot = 0
        for H in list(nx.connected_component_subgraphs(G)):
            if float(len(list(H.nodes()))) == 1.0:
                nbc += 1.0
            nbctot += 1.0
        filew = open(OUTPUT_COMPLET+"-1_connected_components", "a+")
        print >> filew, str(float((nbc/nbctot)*100.0))
        filew.close()

    print_analysis(FILEDATA, "components")



# Biconnected component average diameter (hops)
if ANALYSIS_TYPE == 36:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-biconnected_components_diameter"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbc = 0
        nba = 0
        for H in list(nx.biconnected_component_subgraphs(G)):
            nbc += 1.0
            length=nx.shortest_path_length(H)

            maxl = 0
            for a in set(length.keys()):
                for b in set(length[a].keys()):
                    if(length[a][b] > maxl):
                        maxl = length[a][b]
            nba += float(maxl)

        filew = open(FILEDATA, "a+")
        print >> filew, str(nba/nbc)
        filew.close()

    print_analysis(FILEDATA, "")


# Biconnected component average diameter (meters)
if ANALYSIS_TYPE == 37:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-biconnected_components_diameterm"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        nbc = 0
        nba = 0
        for H in list(nx.biconnected_component_subgraphs(G)):
            DISTANCE_MAX = 0
            dejafait = list(H.nodes())
            for sensor in list(H.nodes()):
                dejafait.remove(sensor)
                for sensorB in dejafait:
                    distance_tmp = math.sqrt(pow(pos[sensor][0]-pos[sensorB][0],2) + pow(pos[sensor][1]-pos[sensorB][1],2))
                    if distance_tmp > DISTANCE_MAX:
                        DISTANCE_MAX = distance_tmp
            nbc += 1.0
            nba += float(DISTANCE_MAX)

        filew = open(FILEDATA, "a+")
        print >> filew, str(nba/nbc)
        filew.close()

    print_analysis(FILEDATA, " meters")




# Average articulation points for each component
if ANALYSIS_TYPE == 16:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-connected_components_ap"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbc = 0
        nba = 0
        for H in list(nx.connected_component_subgraphs(G)):
            nbc += 1.0
            nba += float(len(list(nx.articulation_points(H))))

        filew = open(FILEDATA, "a+")
        print >> filew, str(nba/nbc)
        filew.close()

    print_analysis(FILEDATA, "nodes")


# Average degree
if ANALYSIS_TYPE == 17:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-degree"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbb = 0.0
        nbbc = 0.0
        for a in list(nx.degree(G).values()):
            nbb += a
            nbbc += 1.0

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb/nbbc)
        filew.close()

    print_analysis(FILEDATA, "")
 

# Clustering coefficient
if ANALYSIS_TYPE == 18:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-clustering"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbb = 0
        nbbc = 0
        for a in list(nx.clustering(G).values()):
            nbb += a
            nbbc += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb/nbbc)
        filew.close()

    print_analysis(FILEDATA, "")


# Betweeness centrality
if ANALYSIS_TYPE == 19:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-betweeness_centrality"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbb = 0.0
        nbbc = 0.0
        for a in list(nx.betweenness_centrality(G).values()):
            nbb += a
            nbbc += 1.0

        filew = open(FILEDATA, "a+")
        print >> filew, str(float(nbb/nbbc))
        filew.close()

    print_analysis(FILEDATA, "")


# Pagerank
if ANALYSIS_TYPE == 20:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-pagerank"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        nbb = 0
        nbbc = 0
        for a in list(nx.pagerank(G).values()):
            nbb += a
            nbbc += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb/nbbc)
        filew.close()

    print_analysis(FILEDATA, "")


# Average connected component diameter (weight)
if ANALYSIS_TYPE == 46:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-connected_components_diameterp"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        nbc = 0
        nba = 0
        for H in list(nx.connected_component_subgraphs(G)):
            maxl = 0.0
            dejafait = list(H.nodes())
            for sensorA in list(H.nodes()):
                dejafait.remove(sensorA)
                for sensorB in dejafait:
                    length=float(nx.dijkstra_path_length(H,sensorA,sensorB))
                    if(length > maxl):
                        maxl = length
            nbc += 1.0
            nba += float(maxl)

        filew = open(FILEDATA, "a+")
        print >> filew, str(nba/nbc)
        filew.close()

    print_analysis(FILEDATA, "")










########################### Max connected component









# Max component
if ANALYSIS_TYPE == 21:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_nodes"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        nbb = len(list(H.nodes()))
        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb)
        filew.close()

    print_analysis(FILEDATA, "nodes")


# % of nodes in the max component
if ANALYSIS_TYPE == 22:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_nodes_p"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        nbb = (float(len(list(H.nodes())))/float(len(list(G.nodes()))))*100.0
        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb)
        filew.close()

    print_analysis(FILEDATA, "% of nodes (compared to total nodes)")


# Number of cliques in the max component
if ANALYSIS_TYPE == 23:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_cliques"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        nbcli = 0
        for sensorset in list(nx.find_cliques(H)):
            nbcli += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbcli)
        filew.close()

    print_analysis(FILEDATA, "cliques")


# Max clique in the max component
if ANALYSIS_TYPE == 24:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_max_clique"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        maxc = 0.0
        filew = open(FILEDATA, "a+")
        for sensorset in list(nx.find_cliques(H)):
            if maxc < len(sensorset):
                maxc = len(sensorset)
        print >> filew, maxc
       
        filew.close()

    print_analysis(FILEDATA, "nodes")


# Number of k-cliques in the max component
if ANALYSIS_TYPE == 25:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_"+PARAMSUP+"-cliques"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        nbcli = 0
        for sensorset in list(nx.k_clique_communities(H,int(PARAMSUP))):
            nbcli += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbcli)
        filew.close()

    print_analysis(FILEDATA, PARAMSUP + "-cliques")


# Max component diameter (hops)
if ANALYSIS_TYPE == 27:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_diameter"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]
        length=nx.shortest_path_length(H)

        maxl = 0
        for a in set(length.keys()):
            for b in set(length[a].keys()):
                if(length[a][b] > maxl):
                    maxl = length[a][b]

        filew = open(FILEDATA, "a+")
        print >> filew, str(maxl)
        filew.close()

    print_analysis(FILEDATA, " hops")


# Max component diameter (meters)
if ANALYSIS_TYPE == 28:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_diameterm"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        DISTANCE_MAX = 0
        dejafait = list(H.nodes())
        for sensor in list(H.nodes()):
            dejafait.remove(sensor)
            for sensorB in dejafait:
                distance_tmp = math.sqrt(pow(pos[sensor][0]-pos[sensorB][0],2) + pow(pos[sensor][1]-pos[sensorB][1],2))
                if distance_tmp > DISTANCE_MAX:
                    DISTANCE_MAX = distance_tmp

        filew = open(FILEDATA, "a+")
        print >> filew, str(DISTANCE_MAX)
        filew.close()

    print_analysis(FILEDATA, " meters")


# Articulation points in the max component
if ANALYSIS_TYPE == 30:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_ap"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        print >> filew, str(len(list(nx.articulation_points(H))))
        filew.close()

    print_analysis(FILEDATA, "nodes")


# Average degree in the max component
if ANALYSIS_TYPE == 31:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_degree"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        nbb = 0.0
        nbbc = 0.0
        for a in list(nx.degree(H).values()):
            nbb += a
            nbbc += 1.0

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb/nbbc)
        filew.close()

    print_analysis(FILEDATA, "")


# Clustering coefficient in the max component
if ANALYSIS_TYPE == 32:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_clustering"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        nbb = 0
        nbbc = 0
        for a in list(nx.clustering(H).values()):
            nbb += a
            nbbc += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb/nbbc)
        filew.close()

    print_analysis(FILEDATA, "")


# Betweeness centrality in the max component
if ANALYSIS_TYPE == 33:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_betweeness_centrality"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        nbb = 0
        nbbc = 0
        for a in list(nx.betweenness_centrality(H).values()):
            nbb += a
            nbbc += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb/nbbc)
        filew.close()

    print_analysis(FILEDATA, "")


# Closeness centrality in the max component
if ANALYSIS_TYPE == 43:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_closeness_centrality"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        nbb = 0
        nbbc = 0
        for a in list(nx.closeness_centrality(H).values()):
            nbb += a
            nbbc += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb/nbbc)
        filew.close()

    print_analysis(FILEDATA, "")


# Pagerank in the max component
if ANALYSIS_TYPE == 34:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_pagerank"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        nbb = 0
        nbbc = 0
        for a in list(nx.pagerank(H).values()):
            nbb += a
            nbbc += 1

        filew = open(FILEDATA, "a+")
        print >> filew, str(nbb/nbbc)
        filew.close()

    print_analysis(FILEDATA, "")


# Average connected component diameter in the max connected component (weight)
if ANALYSIS_TYPE == 47:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-mcc_diameterp"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        maxl = 0.0
        dejafait = list(H.nodes())
        for sensorA in list(H.nodes()):
            dejafait.remove(sensorA)
            for sensorB in dejafait:
                length=float(nx.dijkstra_path_length(H,sensorA,sensorB))
                if(length > maxl):
                    maxl = length

        filew = open(FILEDATA, "a+")
        print >> filew, str(maxl)
        filew.close()

    print_analysis(FILEDATA, "")


# Is the max component the largest ? (in term of covered area)
if ANALYSIS_TYPE == 45:

    FILEDATA = OUTPUT_COMPLET+"-is_lcc"
    if not os.path.isfile(FILEDATA):

        tosay = ""
        get_sensors()
        get_links()
        G = get_graph()
        N = int(get_largest_component())
        if N == 0:
            tosay = tosay + "yes: "
        else:
            tosay = tosay + "no: "
            DISTANCE_MAX = 0
            H = nx.connected_component_subgraphs(G)[0]
            dejafait = list(H.nodes())
            for sensor in list(H.nodes()):
                dejafait.remove(sensor)
                for sensorB in dejafait:
                    distance_tmp = math.sqrt(pow(pos[sensor][0]-pos[sensorB][0],2) + pow(pos[sensor][1]-pos[sensorB][1],2))
                    if distance_tmp > DISTANCE_MAX:
                        DISTANCE_MAX = distance_tmp
            tosay = tosay + str(DISTANCE_MAX) + " meters vs "

        DISTANCE_MAX = 0
        H = nx.connected_component_subgraphs(G)[N]

        dejafait = list(H.nodes())
        for sensor in list(H.nodes()):
            dejafait.remove(sensor)
            for sensorB in dejafait:
                distance_tmp = math.sqrt(pow(pos[sensor][0]-pos[sensorB][0],2) + pow(pos[sensor][1]-pos[sensorB][1],2))
                if distance_tmp > DISTANCE_MAX:
                    DISTANCE_MAX = distance_tmp
        tosay = tosay + str(DISTANCE_MAX) + " meters"

        filew = open(FILEDATA, "a+")
        print >> filew, tosay
        filew.close()

    f = open(FILEDATA,'r')
    value = list(f.readlines())
    value = value[0].replace("\n", "")
    f.close()
    print value












########################### CDF
########################### connected components















# CDF - nodes in a clique
if ANALYSIS_TYPE == 100:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_cliques"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for sensorset in list(nx.find_cliques(G)):
            nbsen = 0
            for sen in list(sensorset):
                nbsen += 1
            print >> filew, str(nbsen)
        filew.close()



# CDF - connected components
if ANALYSIS_TYPE == 101:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_connected_components"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for a in list(nx.connected_components(G)):
            print >> filew, len(list(a))
        filew.close()



# CDF - degree
if ANALYSIS_TYPE == 102:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_degree"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for a in list(nx.degree(G).values()):
            print >> filew, str(a)
        filew.close()




# CDF - average connected component diameter (hops)
if ANALYSIS_TYPE == 103:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_connected_components_diameter"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for H in list(nx.connected_component_subgraphs(G)):
            length=nx.shortest_path_length(H)

            maxl = 0
            for a in set(length.keys()):
                for b in set(length[a].keys()):
                    if(length[a][b] > maxl):
                        maxl = length[a][b]
            print >> filew, str(maxl)
        filew.close()


# CDF - average connected component diameter (weights)
if ANALYSIS_TYPE == 115:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_connected_components_diameterp"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for H in list(nx.connected_component_subgraphs(G)):
            maxl = 0.0
            dejafait = list(H.nodes())
            for sensorA in list(H.nodes()):
                dejafait.remove(sensorA)
                for sensorB in dejafait:
                    length=float(nx.dijkstra_path_length(H,sensorA,sensorB))
                    if(length > maxl):
                        maxl = length

            print >> filew, str(maxl)
        filew.close()


# CDF - edges length (weights)
if ANALYSIS_TYPE == 116:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_mcc_distancep"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        dejafait = list(H.nodes())
        for sensorA in list(H.nodes()):
            dejafait.remove(sensorA)
            for sensorB in dejafait:
                print >> filew, str(nx.dijkstra_path_length(H,sensorA,sensorB))
        filew.close()


# CDF - average connected component diameter (meters)
if ANALYSIS_TYPE == 104:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_connected_components_diameterm"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for H in list(nx.connected_component_subgraphs(G)):

            DISTANCE_MAX = 0
            dejafait = list(H.nodes())
            for sensor in list(H.nodes()):
                dejafait.remove(sensor)
                for sensorB in dejafait:
                    distance_tmp = math.sqrt(pow(pos[sensor][0]-pos[sensorB][0],2) + pow(pos[sensor][1]-pos[sensorB][1],2))
                    if distance_tmp > DISTANCE_MAX:
                        DISTANCE_MAX = distance_tmp
            print >> filew, str(DISTANCE_MAX)
        filew.close()



# CDF - average connected component articulation points
if ANALYSIS_TYPE == 105:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_connected_components_ap"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for H in list(nx.connected_component_subgraphs(G)):
            print >> filew, str(len(list(nx.articulation_points(H))))
        filew.close()



# CDF - edges length (meters)
if ANALYSIS_TYPE == 110:

    get_sensors()

    FILEDATA = OUTPUT_COMPLET+"-cdf_edges_length"
    if not os.path.isfile(FILEDATA):
        f = open(CSV_FILE,'r')
        links = f.readlines()
        f.close()
        cpterr = 0
        filew = open(FILEDATA, "a+")
        for link in links:
            try:
                tmptab = link.split(';')
                print >> filew, str(int(math.sqrt(pow(pos[int(tmptab[0])][0]-pos[int(tmptab[1])][0],2) + pow(pos[int(tmptab[0])][1]-pos[int(tmptab[1])][1],2))))
            except IndexError:
                cpterr+=1
            except ValueError:
                cpterr+=1
        filew.close()


# CDF - % of articulation point nodes per connected component
if ANALYSIS_TYPE == 112:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_ap_cc"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        filew = open(FILEDATA, "a+")
        for H in list(nx.connected_component_subgraphs(G)):
            if float(len(list(H.nodes()))) > 2.0:
                print >> filew, str((float(len(list(nx.articulation_points(H))))/float(len(list(H.nodes()))))*100.0)
        filew.close()


# CDF - Betweeness
if ANALYSIS_TYPE == 113:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_betweeness_centrality"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for a in list(nx.betweenness_centrality(G).values()):
            print >> filew, str(a)
        filew.close()

# CDF - closeness
if ANALYSIS_TYPE == 117:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_closeness_centrality"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for a in list(nx.closeness_centrality(G).values()):
            print >> filew, str(a)
        filew.close()

# CDF - clustering coefficient
if ANALYSIS_TYPE == 122:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_clustering_coefficient"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for a in list(nx.clustering(G).values()):
            print >> filew, str(a)
        filew.close()

# CDF - % of the number of nodes
if ANALYSIS_TYPE == 119:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_nodes_p"
    if not os.path.isfile(FILEDATA):
        G = get_graph()

        filew = open(FILEDATA, "a+")
        for H in list(nx.connected_component_subgraphs(G)):
            print >> filew, (float(len(list(H.nodes())))/float(len(list(G.nodes()))))*100.0
        filew.close()


# CDF - Inter-distance between connected components
if ANALYSIS_TYPE == 120:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_distance_cc"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        filew = open(FILEDATA, "a+")

        samecomponent = {}
        cptco = 0
        for H in list(nx.connected_component_subgraphs(G)):
            samecomponent[cptco] = []
            for sensor in list(H.nodes()):
                samecomponent[cptco].append(sensor)
            cptco += 1
        dejafait = list(set(samecomponent.keys()))
        for cptcoA in set(samecomponent.keys()):
            dejafait.remove(cptcoA)
            for cptcoB in dejafait:
                    mindistance = -1
                    for sensorA in samecomponent[cptcoA]:
                        for sensorB in samecomponent[cptcoB]:
                            DISTANCE = math.sqrt(pow(pos[sensorA][0]-pos[sensorB][0],2) + pow(pos[sensorA][1]-pos[sensorB][1],2))
                            if mindistance == -1 or mindistance > DISTANCE:
                                mindistance = DISTANCE
                    print >> filew, str(mindistance)
        filew.close()


# CDF - Minimal inter-distance between connected components 
if ANALYSIS_TYPE == 121:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_min_distance_cc"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        filew = open(FILEDATA, "a+")

        samecomponent = {}
        cptco = 0
        for H in list(nx.connected_component_subgraphs(G)):
            samecomponent[cptco] = []
            for sensor in list(H.nodes()):
                samecomponent[cptco].append(sensor)
            cptco += 1
        dejafait = list(set(samecomponent.keys()))
        for cptcoA in set(samecomponent.keys()):
            dejafait.remove(cptcoA)
            mindistance = -1
            for cptcoB in dejafait:
                for sensorA in samecomponent[cptcoA]:
                    for sensorB in samecomponent[cptcoB]:
                        DISTANCE = math.sqrt(pow(pos[sensorA][0]-pos[sensorB][0],2) + pow(pos[sensorA][1]-pos[sensorB][1],2))
                        if mindistance == -1 or mindistance > DISTANCE:
                            mindistance = DISTANCE
            if(mindistance > -1):
                print >> filew, str(mindistance)
        filew.close()


# CDF - Inter-distance between connected components based on Delaunay triangulation
if ANALYSIS_TYPE == 150:

    FILEDATA = OUTPUT_COMPLET+"-cdf_delaunay_distance_cc"
    if not os.path.isfile(FILEDATA):
        get_sensors()
        get_links()
        G = get_graph()

        posgraph=[]
        for sensorset in list(nx.connected_components(G)):
            cpt_avg = 0.0
            cpt_avgx = 0.0
            cpt_avgy = 0.0
            for sensor in sensorset:
                cpt_avgx += pos[sensor][0]
                cpt_avgy += pos[sensor][1]
                cpt_avg += 1.0
            posgraph.append((cpt_avgx/cpt_avg,cpt_avgy/cpt_avg))

        delTri = scipy.spatial.Delaunay(posgraph)
        edges = set() 
        for n in xrange(delTri.nsimplex): 
            edge = sorted([delTri.vertices[n,0], delTri.vertices[n,1]]) 
            edges.add((edge[0], edge[1])) 
            edge = sorted([delTri.vertices[n,0], delTri.vertices[n,2]]) 
            edges.add((edge[0], edge[1])) 
            edge = sorted([delTri.vertices[n,1], delTri.vertices[n,2]]) 
            edges.add((edge[0], edge[1])) 

        D = nx.Graph(list(edges))

        filew = open(FILEDATA, "a+")
        for edge in list(D.edges()):
            print >> filew, str(int(math.sqrt(pow(posgraph[edge[0]][0]-posgraph[edge[1]][0],2) + pow(posgraph[edge[0]][1]-posgraph[edge[1]][1],2))))
        filew.close()











########################### CDF - max connected component











# CDF - nodes in a clique (max component)
if ANALYSIS_TYPE == 106:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_mcc_cliques"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        for sensorset in list(nx.find_cliques(H)):
            nbsen = 0
            for sen in list(sensorset):
                nbsen += 1
            print >> filew, str(nbsen)
        filew.close()



# CDF - degree (max component)
if ANALYSIS_TYPE == 107:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_mcc_degree"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        for a in list(nx.degree(H).values()):
            print >> filew, str(a)
        filew.close()



# CDF - inter-distance (max component)
if ANALYSIS_TYPE == 108:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_mcc_distance"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        for node in list(H.nodes()):
            length=nx.shortest_path_length(H,source=node)
            for a in set(length.keys()):
                print >> filew, str(length[a])
        filew.close()



# CDF - inter-distance (meters) (max component)
if ANALYSIS_TYPE == 109:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_mcc_distancem"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        dejafait = list(H.nodes())
        for sensor in list(H.nodes()):
            dejafait.remove(sensor)
            for sensorB in dejafait:
                print >> filew, str(int(math.sqrt(pow(pos[sensor][0]-pos[sensorB][0],2) + pow(pos[sensor][1]-pos[sensorB][1],2))))
        filew.close()


# CDF edges length (meters) (max component)
if ANALYSIS_TYPE == 111:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_mcc_edges_length"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        for edge in list(H.edges()):
            print >> filew, str(int(math.sqrt(pow(pos[edge[0]][0]-pos[edge[1]][0],2) + pow(pos[edge[0]][1]-pos[edge[1]][1],2))))
        filew.close()


# CDF - betweeness (max component)
if ANALYSIS_TYPE == 114:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_mcc_betweeness_centrality"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        for a in list(nx.betweenness_centrality(H).values()):
            print >> filew, str(a)
        filew.close()


# CDF - closeness (max component)
if ANALYSIS_TYPE == 118:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_mcc_closeness_centrality"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        for a in list(nx.closeness_centrality(H).values()):
            print >> filew, str(a)
        filew.close()

# CDF - clustering coefficient (max component)
if ANALYSIS_TYPE == 123:

    get_sensors()
    get_links()

    FILEDATA = OUTPUT_COMPLET+"-cdf_mcc_clustering_coefficient"
    if not os.path.isfile(FILEDATA):
        G = get_graph()
        H = nx.connected_component_subgraphs(G)[0]

        filew = open(FILEDATA, "a+")
        for a in list(nx.clustering(H).values()):
            print >> filew, str(a)
        filew.close()

# EOF